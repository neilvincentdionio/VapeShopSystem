package com.example.vapeshop;

import android.graphics.Color;
import android.os.Bundle;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.GridLayout;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import org.json.JSONObject;

import java.util.List;
import java.util.Map;

public class RiderDashboardFragment extends Fragment {

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_rider_dashboard, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        MainActivity activity = (MainActivity) requireActivity();
        ((TextView) view.findViewById(R.id.rider_welcome_title)).setText(
            "Welcome back, " + activity.getRegisteredFullName() + "!"
        );
        view.findViewById(R.id.rider_dashboard_notif_btn).setOnClickListener(v ->
            activity.openNotificationsScreen());
        view.findViewById(R.id.rider_view_all_deliveries).setOnClickListener(v ->
            activity.loadRiderFragment(new RiderDeliveriesFragment()));
        view.findViewById(R.id.rider_view_all_returns).setOnClickListener(v ->
            activity.loadRiderFragment(new RiderReturnsFragment()));
        loadData(view, activity);
    }

    @Override
    public void onResume() {
        super.onResume();
        View v = getView();
        if (v != null) {
            MainActivity activity = (MainActivity) requireActivity();
            activity.refreshNotificationBadges();
            loadData(v, activity);
        }
    }

    private void loadData(View view, MainActivity activity) {
        activity.fetchRiderOrders("active", new MainActivity.SimpleCallback() {
            @Override
            public void onSuccess(String body) {
                List<JSONObject> deliveries = RiderOrderUi.parseOrders(body);
                activity.fetchRiderOrders("returns", new MainActivity.SimpleCallback() {
                    @Override
                    public void onSuccess(String body2) {
                        if (!isAdded()) {
                            return;
                        }
                        List<JSONObject> returns = RiderOrderUi.parseOrders(body2);
                        bindStats(view, deliveries, returns);
                        bindRecent(view, deliveries, returns, activity);
                    }

                    @Override
                    public void onError(String message) {
                        if (isAdded()) {
                            RiderOrderUi.toast(requireContext(), message);
                        }
                    }
                });
            }

            @Override
            public void onError(String message) {
                if (isAdded()) {
                    RiderOrderUi.toast(requireContext(), message);
                }
            }
        });
    }

    private void bindStats(View view, List<JSONObject> deliveries, List<JSONObject> returns) {
        Map<String, Integer> d = RiderOrderUi.computeDeliveryStats(deliveries);
        Map<String, Integer> r = RiderOrderUi.computeReturnStats(returns);
        GridLayout grid = view.findViewById(R.id.rider_stats_grid);
        grid.removeAllViews();
        addStatChip(grid, String.valueOf(d.get("active")), "Active", "#0F766E");
        addStatChip(grid, String.valueOf(d.get("to_ship")), "For pickup", "#0F766E");
        addStatChip(grid, String.valueOf(d.get("to_receive")), "Out for delivery", "#0F766E");
        addStatChip(grid, String.valueOf(d.get("completed_today")), "Done today", "#0F766E");
        addStatChip(grid, String.valueOf(r.get("return_pickups")), "Return pickups", "#D97706");
        addStatChip(grid, String.valueOf(r.get("return_picked_up")), "Returns picked up", "#D97706");
    }

    private void addStatChip(GridLayout grid, String value, String label, String color) {
        View chip = LayoutInflater.from(requireContext()).inflate(R.layout.item_rider_stat_chip, grid, false);
        GridLayout.LayoutParams lp = new GridLayout.LayoutParams();
        lp.width = 0;
        lp.height = GridLayout.LayoutParams.WRAP_CONTENT;
        lp.columnSpec = GridLayout.spec(GridLayout.UNDEFINED, 1f);
        lp.setGravity(Gravity.FILL_HORIZONTAL);
        chip.setLayoutParams(lp);
        TextView valueView = chip.findViewById(R.id.rider_stat_value);
        TextView labelView = chip.findViewById(R.id.rider_stat_label);
        valueView.setText(value);
        valueView.setTextColor(Color.parseColor(color));
        labelView.setText(label);
        grid.addView(chip);
    }

    private void bindRecent(View view, List<JSONObject> deliveries, List<JSONObject> returns, MainActivity activity) {
        LinearLayout dContainer = view.findViewById(R.id.rider_recent_deliveries);
        LinearLayout rContainer = view.findViewById(R.id.rider_recent_returns);
        dContainer.removeAllViews();
        rContainer.removeAllViews();

        int dCount = Math.min(3, deliveries.size());
        if (dCount == 0) {
            addEmpty(dContainer, "No recent deliveries.");
        } else {
            for (int i = 0; i < dCount; i++) {
                JSONObject order = deliveries.get(i);
                dContainer.addView(RiderOrderUi.bindCompactRow(requireContext(), order, false, v ->
                    activity.openRiderOrderDetail(order)));
            }
        }

        int rCount = Math.min(3, returns.size());
        if (rCount == 0) {
            addEmpty(rContainer, "No return pickups assigned.");
        } else {
            for (int i = 0; i < rCount; i++) {
                JSONObject order = returns.get(i);
                rContainer.addView(RiderOrderUi.bindCompactRow(requireContext(), order, true, v ->
                    activity.openRiderOrderDetail(order)));
            }
        }
    }

    private void addEmpty(LinearLayout parent, String text) {
        TextView tv = new TextView(requireContext());
        tv.setText(text);
        tv.setTextColor(Color.parseColor("#6B7280"));
        tv.setTextSize(12f);
        parent.addView(tv);
    }
}
