package com.example.vapeshop;

import android.os.Bundle;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import org.json.JSONArray;
import org.json.JSONObject;

public class RiderProfileFragment extends Fragment {

    private Button deleteDeliveriesButton;
    private Button deleteReturnsButton;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_rider_profile, container, false);
        MainActivity activity = (MainActivity) requireActivity();

        EditText nameInput = view.findViewById(R.id.rider_profile_name);
        EditText emailInput = view.findViewById(R.id.rider_profile_email);
        EditText phoneInput = view.findViewById(R.id.rider_profile_phone);
        EditText passwordInput = view.findViewById(R.id.rider_profile_password);
        EditText confirmInput = view.findViewById(R.id.rider_profile_password_confirm);
        deleteDeliveriesButton = view.findViewById(R.id.rider_profile_delete_deliveries);
        deleteReturnsButton = view.findViewById(R.id.rider_profile_delete_returns);

        nameInput.setText(activity.getRegisteredFullName());
        emailInput.setText(activity.getRegisteredEmail());
        phoneInput.setText(activity.getRegisteredPhone());

        deleteDeliveriesButton.setOnClickListener(v -> confirmDismiss(
            "Delete All Completed Deliveries",
            "Remove all completed deliveries from your list?",
            true
        ));
        deleteReturnsButton.setOnClickListener(v -> confirmDismiss(
            "Delete All Completed Returns",
            "Remove all completed returns from your list?",
            false
        ));
        refreshCompletedCounts(activity);

        view.findViewById(R.id.rider_profile_save).setOnClickListener(v -> {
            String name = nameInput.getText() == null ? "" : nameInput.getText().toString().trim();
            String email = emailInput.getText() == null ? "" : emailInput.getText().toString().trim();
            String phone = phoneInput.getText() == null ? "" : phoneInput.getText().toString().trim();
            String pass = passwordInput.getText() == null ? "" : passwordInput.getText().toString();
            String confirm = confirmInput.getText() == null ? "" : confirmInput.getText().toString();

            if (name.isEmpty() || email.isEmpty()) {
                RiderOrderUi.toast(requireContext(), "Name and email are required.");
                return;
            }
            if (!pass.isEmpty() && pass.length() < 6) {
                RiderOrderUi.toast(requireContext(), "Password must be at least 6 characters.");
                return;
            }
            if (!pass.isEmpty() && !pass.equals(confirm)) {
                RiderOrderUi.toast(requireContext(), "Password confirmation does not match.");
                return;
            }
            activity.updateProfileWithServer(name, email, new MainActivity.SimpleCallback() {
                @Override
                public void onSuccess(String message) {
                    if (!TextUtils.isEmpty(pass)) {
                        activity.updatePasswordWithServer(
                            activity.getStoredPassword(),
                            pass,
                            new MainActivity.SimpleCallback() {
                                @Override
                                public void onSuccess(String msg) {
                                    RiderOrderUi.toast(requireContext(), msg);
                                }

                                @Override
                                public void onError(String err) {
                                    RiderOrderUi.toast(requireContext(), err);
                                }
                            }
                        );
                    } else {
                        RiderOrderUi.toast(requireContext(), message);
                    }
                }

                @Override
                public void onError(String message) {
                    RiderOrderUi.toast(requireContext(), message);
                }
            });
        });

        view.findViewById(R.id.rider_profile_logout).setOnClickListener(v -> activity.onLogout());
        return view;
    }

    @Override
    public void onResume() {
        super.onResume();
        if (isAdded() && getActivity() instanceof MainActivity) {
            refreshCompletedCounts((MainActivity) requireActivity());
        }
    }

    private void refreshCompletedCounts(MainActivity activity) {
        activity.fetchRiderOrders("active", new MainActivity.SimpleCallback() {
            @Override
            public void onSuccess(String body) {
                if (!isAdded()) {
                    return;
                }
                int deliveryCount = countByStatus(RiderOrderUi.parseOrders(body), "completed");
                updateDeleteButton(deleteDeliveriesButton, "Delete All Completed Deliveries", deliveryCount);
            }

            @Override
            public void onError(String message) {
                updateDeleteButton(deleteDeliveriesButton, "Delete All Completed Deliveries", 0);
            }
        });

        activity.fetchRiderOrders("returns", new MainActivity.SimpleCallback() {
            @Override
            public void onSuccess(String body) {
                if (!isAdded()) {
                    return;
                }
                int returnCount = countByStatus(RiderOrderUi.parseOrders(body), "return_refund");
                updateDeleteButton(deleteReturnsButton, "Delete All Completed Returns", returnCount);
            }

            @Override
            public void onError(String message) {
                updateDeleteButton(deleteReturnsButton, "Delete All Completed Returns", 0);
            }
        });
    }

    private static int countByStatus(java.util.List<JSONObject> orders, String status) {
        int count = 0;
        for (JSONObject order : orders) {
            if (status.equals(order.optString("delivery_status", ""))) {
                count++;
            }
        }
        return count;
    }

    private void updateDeleteButton(Button button, String label, int count) {
        if (button == null) {
            return;
        }
        button.setText(count > 0 ? label + " (" + count + ")" : label);
        button.setEnabled(count > 0);
        button.setAlpha(count > 0 ? 1f : 0.55f);
    }

    private void confirmDismiss(String title, String message, boolean deliveries) {
        if (!isAdded()) {
            return;
        }
        new androidx.appcompat.app.AlertDialog.Builder(requireContext())
            .setTitle(title)
            .setMessage(message)
            .setPositiveButton("Delete All", (d, w) -> {
                MainActivity activity = (MainActivity) requireActivity();
                MainActivity.SimpleCallback callback = new MainActivity.SimpleCallback() {
                    @Override
                    public void onSuccess(String body) {
                        if (!isAdded()) {
                            return;
                        }
                        String toastMsg = parseDismissMessage(body);
                        RiderOrderUi.toast(requireContext(), toastMsg);
                        refreshCompletedCounts(activity);
                    }

                    @Override
                    public void onError(String err) {
                        if (isAdded()) {
                            RiderOrderUi.toast(requireContext(), err);
                        }
                    }
                };
                if (deliveries) {
                    activity.dismissRiderCompletedDeliveries(callback);
                } else {
                    activity.dismissRiderCompletedReturns(callback);
                }
            })
            .setNegativeButton(android.R.string.cancel, null)
            .show();
    }

    private static String parseDismissMessage(String body) {
        try {
            JSONObject root = new JSONObject(body);
            String message = root.optString("message", "");
            if (!message.isEmpty()) {
                return message;
            }
        } catch (Exception ignored) {
            // Fall through.
        }
        return "Completed items cleared.";
    }
}
