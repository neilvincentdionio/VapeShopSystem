package com.example.vapeshop;

import android.graphics.Color;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.webkit.WebView;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import org.json.JSONObject;

public class RiderOrderDetailFragment extends Fragment {

    private static final String ARG_ORDER_JSON = "order_json";

    private JSONObject order;
    private WebView mapWebView;
    private TextView trackingMeta;
    private int orderId;

    public static RiderOrderDetailFragment newInstance(JSONObject order) {
        RiderOrderDetailFragment f = new RiderOrderDetailFragment();
        Bundle args = new Bundle();
        args.putString(ARG_ORDER_JSON, order.toString());
        f.setArguments(args);
        return f;
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_rider_order_detail, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        try {
            String raw = getArguments() != null ? getArguments().getString(ARG_ORDER_JSON, "{}") : "{}";
            order = new JSONObject(raw);
        } catch (Exception e) {
            order = new JSONObject();
        }
        orderId = order.optInt("order_id", 0);
        MainActivity activity = (MainActivity) requireActivity();
        String status = order.optString("delivery_status", "");
        final boolean isReturn = status.startsWith("return_");

        view.findViewById(R.id.rider_detail_back).setOnClickListener(v -> {
            if (isReturn) {
                activity.loadRiderFragment(new RiderReturnsFragment());
            } else {
                activity.loadRiderFragment(new RiderDeliveriesFragment());
            }
        });

        TextView typeTag = view.findViewById(R.id.rider_detail_type_tag);
        if (isReturn) {
            typeTag.setVisibility(View.VISIBLE);
            typeTag.setText("RETURN / REFUND");
            typeTag.setBackgroundColor(Color.parseColor("#FEF3C7"));
            typeTag.setTextColor(Color.parseColor("#92400E"));
        } else {
            typeTag.setVisibility(View.GONE);
        }

        ((TextView) view.findViewById(R.id.rider_detail_reference)).setText(
            order.optString("reference_number", "Order"));
        TextView statusView = view.findViewById(R.id.rider_detail_status);
        statusView.setText(isReturn
            ? RiderOrderUi.getReturnStatusLabel(order, status)
            : RiderOrderUi.getStatusLabel(status));
        RiderOrderUi.applyStatusBadge(requireContext(), statusView, status, isReturn, order);

        ((TextView) view.findViewById(R.id.rider_detail_customer)).setText(
            order.optString("customer_name", "—"));
        ((TextView) view.findViewById(R.id.rider_detail_address)).setText(RiderOrderUi.getShipmentAddress(order));

        RiderOrderUi.bindCopyableContact(
            requireContext(),
            view.findViewById(R.id.rider_detail_contact),
            view.findViewById(R.id.rider_detail_copy_contact),
            order
        );

        mapWebView = view.findViewById(R.id.rider_detail_map);
        trackingMeta = view.findViewById(R.id.rider_detail_tracking_meta);

        LinearLayout actions = view.findViewById(R.id.rider_detail_actions);
        Runnable reload = () -> {
            if (!isAdded()) {
                return;
            }
            activity.loadRiderFragment(RiderOrderDetailFragment.newInstance(order));
        };
        RiderOrderUi.populateActions(requireContext(), actions, order, isReturn, activity.createRiderActionHandler(reload));

        if (mapWebView != null) {
            mapWebView.post(() -> {
                if (!isAdded() || mapWebView == null) {
                    return;
                }
                try {
                    activity.setupLiveTrackingWebView(mapWebView);
                    if (orderId > 0) {
                        activity.startRiderTrackingForOrder(orderId, mapWebView, trackingMeta);
                    } else if (trackingMeta != null) {
                        trackingMeta.setText("Map unavailable for this order.");
                    }
                } catch (Exception e) {
                    android.util.Log.e("QuickPuff", "Rider map failed", e);
                    if (trackingMeta != null) {
                        trackingMeta.setText("Map unavailable on this device.");
                    }
                }
            });
        }
    }

    @Override
    public void onDestroyView() {
        MainActivity activity = (MainActivity) getActivity();
        if (activity != null) {
            activity.stopRiderTracking();
        }
        super.onDestroyView();
    }
}
