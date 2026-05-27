package com.example.vapeshop;

import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

/**
 * Shared rider deliveries / returns list with search and auto-refresh.
 */
public abstract class RiderOrderListFragment extends Fragment {

    private static final long AUTO_REFRESH_MS = 15000L;

    protected final List<JSONObject> allOrders = new ArrayList<>();
    protected LinearLayout listContainer;
    protected EditText searchInput;

    private final Handler refreshHandler = new Handler(Looper.getMainLooper());
    private boolean autoRefreshActive = false;
    private final Runnable autoRefreshRunnable = new Runnable() {
        @Override
        public void run() {
            if (isAdded() && isResumed()) {
                loadOrders(false);
            }
            if (autoRefreshActive) {
                refreshHandler.postDelayed(this, AUTO_REFRESH_MS);
            }
        }
    };

    protected abstract String getListTitle();

    protected abstract String getListSubtitle();

    protected abstract String getOrdersListType();

    protected abstract boolean isReturnList();

    protected abstract String getEmptyMessage();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_rider_order_list, container, false);
        ((TextView) view.findViewById(R.id.rider_list_title)).setText(getListTitle());
        ((TextView) view.findViewById(R.id.rider_list_subtitle)).setText(getListSubtitle());
        listContainer = view.findViewById(R.id.rider_list_container);
        searchInput = view.findViewById(R.id.rider_list_search);
        searchInput.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) { renderList(); }
            @Override public void afterTextChanged(Editable s) {}
        });
        loadOrders(true);
        return view;
    }

    @Override
    public void onResume() {
        super.onResume();
        startAutoRefresh();
        loadOrders(false);
    }

    @Override
    public void onPause() {
        stopAutoRefresh();
        super.onPause();
    }

    private void startAutoRefresh() {
        autoRefreshActive = true;
        refreshHandler.removeCallbacks(autoRefreshRunnable);
        refreshHandler.postDelayed(autoRefreshRunnable, AUTO_REFRESH_MS);
    }

    private void stopAutoRefresh() {
        autoRefreshActive = false;
        refreshHandler.removeCallbacks(autoRefreshRunnable);
    }

    protected void loadOrders(boolean showErrors) {
        if (!isAdded()) {
            return;
        }
        MainActivity activity = (MainActivity) requireActivity();
        activity.fetchRiderOrders(getOrdersListType(), new MainActivity.SimpleCallback() {
            @Override
            public void onSuccess(String body) {
                if (!isAdded()) {
                    return;
                }
                allOrders.clear();
                allOrders.addAll(RiderOrderUi.parseOrders(body));
                renderList();
            }

            @Override
            public void onError(String message) {
                if (isAdded() && showErrors) {
                    RiderOrderUi.toast(requireContext(), message);
                }
            }
        });
    }

    protected void renderList() {
        if (!isAdded() || listContainer == null) {
            return;
        }
        listContainer.removeAllViews();
        String q = searchInput != null && searchInput.getText() != null
            ? searchInput.getText().toString() : "";
        MainActivity activity = (MainActivity) requireActivity();
        int shown = 0;
        for (JSONObject order : allOrders) {
            if (!RiderOrderUi.matchesSearch(order, q)) {
                continue;
            }
            shown++;
            View card = getLayoutInflater().inflate(R.layout.item_rider_order_card, listContainer, false);
            RiderOrderUi.bindOrderCard(
                requireContext(),
                card,
                order,
                isReturnList(),
                activity.createRiderActionHandler(() -> loadOrders(false))
            );
            listContainer.addView(card);
        }
        if (shown == 0) {
            TextView empty = new TextView(requireContext());
            empty.setText(allOrders.isEmpty() ? getEmptyMessage() : "No matches for your search.");
            empty.setTextColor(0xFF6B7280);
            listContainer.addView(empty);
        }
    }
}
