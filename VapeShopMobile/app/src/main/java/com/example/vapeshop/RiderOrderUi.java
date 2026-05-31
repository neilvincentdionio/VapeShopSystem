package com.example.vapeshop;

import android.content.ClipData;
import android.content.ClipboardManager;
import android.content.Context;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONObject;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;

/**
 * Shared rider order UI helpers (deliveries, returns, dashboard).
 */
public final class RiderOrderUi {

    public interface RiderActionHandler {
        void onStatusUpdate(int orderId, String status, Map<String, String> extra);

        void onDelivered(int orderId);

        void onReschedule(int orderId);

        void onCancel(int orderId);

        void onScanReturnQr(int orderId);

        void onOpenDetail(JSONObject order);

        void onRefresh();
    }

    private RiderOrderUi() {
    }

    public static List<JSONObject> parseOrders(String responseBody) {
        List<JSONObject> list = new ArrayList<>();
        try {
            JSONObject root = new JSONObject(responseBody);
            JSONArray orders = root.optJSONObject("data") != null
                ? root.optJSONObject("data").optJSONArray("orders") : null;
            if (orders == null) {
                return list;
            }
            for (int i = 0; i < orders.length(); i++) {
                JSONObject o = orders.optJSONObject(i);
                if (o != null) {
                    list.add(o);
                }
            }
        } catch (Exception ignored) {
            // empty
        }
        return list;
    }

    public static String getStatusLabel(String status) {
        if (status == null || status.isEmpty()) {
            return "Unknown";
        }
        switch (status.toLowerCase(Locale.US)) {
            case "to_ship":
                return "For Pickup";
            case "ready_for_pickup":
                return "Ready for Pickup";
            case "accepted_by_rider":
                return "Accepted by Rider";
            case "delivered_to_rider":
                return "Picked Up from Store";
            case "to_receive":
                return "Out for Delivery";
            case "delivered":
                return "Delivered (Await Confirm)";
            case "completed":
                return "Completed";
            case "failed_delivery":
                return "Failed Delivery";
            case "cancelled":
                return "Cancelled";
            case "return_approved":
                return "Return Approved";
            case "return_picked_up":
                return "Return Picked Up";
            case "return_refund":
                return "Refund Completed";
            default:
                return status.replace('_', ' ');
        }
    }

    public static boolean riderHasAcceptedReturnPickup(JSONObject order) {
        JSONObject meta = order != null ? order.optJSONObject("return_meta") : null;
        if (meta == null) {
            return false;
        }
        if (!meta.optString("rider_accepted_pickup_at", "").trim().isEmpty()) {
            return true;
        }
        return meta.optBoolean("rider_accepted_pickup", false);
    }

    public static String getReturnStatusLabel(JSONObject order, String status) {
        if ("return_refund".equals(status)) {
            return "Complete";
        }
        if ("return_picked_up".equals(status)) {
            return "Picked Up";
        }
        if (riderHasAcceptedReturnPickup(order)) {
            return "Ready to Scan QR";
        }
        return "Awaiting Your Approval";
    }

    public static int countCompletedReturns(List<JSONObject> returns) {
        int count = 0;
        for (JSONObject order : returns) {
            if ("return_refund".equals(order.optString("delivery_status", ""))) {
                count++;
            }
        }
        return count;
    }

    public static String getShipmentAddress(JSONObject order) {
        JSONObject shipment = order.optJSONObject("shipment");
        String addr = shipment != null ? shipment.optString("shipping_address", "") : "";
        if (addr == null || addr.trim().isEmpty()) {
            return "No address provided";
        }
        return addr.trim();
    }

    public static String getContact(JSONObject order) {
        JSONObject shipment = order.optJSONObject("shipment");
        String c = shipment != null ? shipment.optString("contact_number", "") : "";
        if (c == null || c.isEmpty()) {
            c = order.optString("customer_phone", "");
        }
        return c == null || c.isEmpty() ? "—" : c.trim();
    }

    public static void copyContactToClipboard(Context context, String phone) {
        if (phone == null || phone.isEmpty() || "—".equals(phone)) {
            toast(context, "No contact number to copy.");
            return;
        }
        ClipboardManager clipboard = (ClipboardManager) context.getSystemService(Context.CLIPBOARD_SERVICE);
        if (clipboard == null) {
            toast(context, "Unable to copy.");
            return;
        }
        clipboard.setPrimaryClip(ClipData.newPlainText("customer_phone", phone));
        toast(context, "Contact number copied");
    }

    public static void bindCopyableContact(Context context, TextView contactView, View copyButton, JSONObject order) {
        String phone = getContact(order);
        contactView.setText(phone);
        Runnable copyAction = () -> copyContactToClipboard(context, phone);
        if (copyButton != null) {
            copyButton.setOnClickListener(v -> copyAction.run());
            boolean hasPhone = phone != null && !phone.isEmpty() && !"—".equals(phone);
            copyButton.setEnabled(hasPhone);
            copyButton.setAlpha(hasPhone ? 1f : 0.4f);
        }
        contactView.setOnLongClickListener(v -> {
            copyAction.run();
            return true;
        });
    }

    public static boolean matchesSearch(JSONObject order, String query) {
        if (query == null || query.trim().isEmpty()) {
            return true;
        }
        String q = query.trim().toLowerCase(Locale.US);
        StringBuilder blob = new StringBuilder();
        blob.append(order.optString("reference_number", "")).append(' ');
        blob.append(order.optString("customer_name", "")).append(' ');
        blob.append(getShipmentAddress(order)).append(' ');
        blob.append(getContact(order)).append(' ');
        blob.append(order.optString("delivery_status", ""));
        return blob.toString().toLowerCase(Locale.US).contains(q);
    }

    public static Map<String, Integer> computeDeliveryStats(List<JSONObject> deliveries) {
        Map<String, Integer> stats = new HashMap<>();
        stats.put("active", 0);
        stats.put("to_ship", 0);
        stats.put("to_receive", 0);
        stats.put("completed_today", 0);
        String today = new SimpleDateFormat("yyyy-MM-dd", Locale.US).format(new Date());
        for (JSONObject order : deliveries) {
            String status = order.optString("delivery_status", "");
            if (java.util.Arrays.asList("to_ship", "to_receive", "failed_delivery", "ready_for_pickup",
                "accepted_by_rider", "delivered_to_rider").contains(status)) {
                stats.put("active", stats.get("active") + 1);
            }
            if ("to_ship".equals(status) || "ready_for_pickup".equals(status)) {
                stats.put("to_ship", stats.get("to_ship") + 1);
            }
            if ("to_receive".equals(status)) {
                stats.put("to_receive", stats.get("to_receive") + 1);
            }
            String updated = order.optString("updated_at", "");
            if ("completed".equals(status) && updated.startsWith(today)) {
                stats.put("completed_today", stats.get("completed_today") + 1);
            }
        }
        return stats;
    }

    public static Map<String, Integer> computeReturnStats(List<JSONObject> returns) {
        Map<String, Integer> stats = new HashMap<>();
        stats.put("return_pickups", 0);
        stats.put("return_picked_up", 0);
        for (JSONObject order : returns) {
            String status = order.optString("delivery_status", "");
            if ("return_approved".equals(status)) {
                stats.put("return_pickups", stats.get("return_pickups") + 1);
            }
            if ("return_picked_up".equals(status)) {
                stats.put("return_picked_up", stats.get("return_picked_up") + 1);
            }
        }
        return stats;
    }

    public static View bindCompactRow(Context context, JSONObject order, boolean isReturn, View.OnClickListener click) {
        LinearLayout row = new LinearLayout(context);
        row.setOrientation(LinearLayout.VERTICAL);
        int pad = (int) (12 * context.getResources().getDisplayMetrics().density);
        row.setPadding(pad, pad, pad, pad);
        row.setBackgroundResource(R.drawable.bg_login_card);
        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        lp.bottomMargin = (int) (8 * context.getResources().getDisplayMetrics().density);
        row.setLayoutParams(lp);

        String status = order.optString("delivery_status", "");
        TextView ref = new TextView(context);
        ref.setText(order.optString("reference_number", "Order"));
        ref.setTextColor(Color.parseColor("#111827"));
        ref.setTextSize(14f);
        ref.setTypeface(null, android.graphics.Typeface.BOLD);
        row.addView(ref);

        TextView meta = new TextView(context);
        meta.setText((isReturn ? getReturnStatusLabel(order, status) : getStatusLabel(status))
            + " · " + order.optString("customer_name", "Customer"));
        meta.setTextColor(Color.parseColor("#6B7280"));
        meta.setTextSize(12f);
        row.addView(meta);

        row.setOnClickListener(click);
        return row;
    }

    public static void bindOrderCard(
        Context context,
        View card,
        JSONObject order,
        boolean isReturnList,
        RiderActionHandler handler
    ) {
        int orderId = order.optInt("order_id", 0);
        String status = order.optString("delivery_status", "");

        TextView typeTag = card.findViewById(R.id.rider_card_type_tag);
        if (isReturnList) {
            typeTag.setVisibility(View.VISIBLE);
            typeTag.setText("RETURN / REFUND");
            typeTag.setBackgroundColor(Color.parseColor("#FEF3C7"));
            typeTag.setTextColor(Color.parseColor("#92400E"));
        } else {
            typeTag.setVisibility(View.GONE);
        }

        ((TextView) card.findViewById(R.id.rider_card_reference)).setText(
            order.optString("reference_number", "Order #" + orderId));
        double amount = order.optDouble("total_amount", 0);
        TextView amountView = card.findViewById(R.id.rider_card_amount);
        if (amountView != null) {
            amountView.setText(String.format(Locale.US, "₱%.2f", amount));
        }
        TextView statusView = card.findViewById(R.id.rider_card_status);
        statusView.setText(isReturnList ? getReturnStatusLabel(order, status) : getStatusLabel(status));
        applyStatusBadge(context, statusView, status, isReturnList, order);
        ((TextView) card.findViewById(R.id.rider_card_customer)).setText(
            order.optString("customer_name", "—"));
        ((TextView) card.findViewById(R.id.rider_card_address)).setText(getShipmentAddress(order));
        TextView contactView = card.findViewById(R.id.rider_card_contact);
        View copyBtn = card.findViewById(R.id.rider_card_copy_contact);
        contactView.setText("Contact: " + getContact(order));
        if (copyBtn != null) {
            String phone = getContact(order);
            copyBtn.setOnClickListener(v -> copyContactToClipboard(context, phone));
            contactView.setOnLongClickListener(v -> {
                copyContactToClipboard(context, phone);
                return true;
            });
        }

        LinearLayout actions = card.findViewById(R.id.rider_card_actions);
        actions.removeAllViews();
        populateActions(context, actions, order, isReturnList, handler);

        card.findViewById(R.id.rider_card_open_detail).setOnClickListener(v -> {
            if (handler != null) {
                handler.onOpenDetail(order);
            }
        });
    }

    public static void populateActions(
        Context context,
        LinearLayout actions,
        JSONObject order,
        boolean isReturnList,
        RiderActionHandler handler
    ) {
        if (handler == null) {
            return;
        }
        int orderId = order.optInt("order_id", 0);
        String status = order.optString("delivery_status", "");

        if (!isReturnList) {
            if ("ready_for_pickup".equals(status)) {
                addActionRow(
                    context,
                    actions,
                    new String[] {"Accept Delivery", "Decline"},
                    new String[] {"#0F766E", "#DC2626"},
                    new Runnable[] {
                        () -> handler.onStatusUpdate(orderId, "accepted_by_rider", null),
                        () -> handler.onStatusUpdate(orderId, "decline_assignment", null)
                    }
                );
            }
            if ("accepted_by_rider".equals(status)) {
                addActionBtn(context, actions, "Picked Up from Store", "#0F766E", () ->
                    handler.onStatusUpdate(orderId, "delivered_to_rider", null));
            }
            if ("delivered_to_rider".equals(status) || "failed_delivery".equals(status)) {
                addActionBtn(context, actions, "Start Delivery", "#1976D2", () ->
                    handler.onStatusUpdate(orderId, "to_receive", null));
            }
            if ("to_receive".equals(status)) {
                addActionRow(
                    context,
                    actions,
                    new String[] {"Delivered", "Reschedule", "Cancel"},
                    new String[] {"#22C55E", "#F59E0B", "#DC2626"},
                    new Runnable[] {
                        () -> handler.onDelivered(orderId),
                        () -> handler.onReschedule(orderId),
                        () -> handler.onCancel(orderId)
                    }
                );
            }
            if ("completed".equals(status)) {
                TextView note = new TextView(context);
                note.setText("Delivery completed.");
                note.setTextColor(Color.parseColor("#1565C0"));
                note.setTextSize(12f);
                actions.addView(note);
            }
            return;
        }

        if ("return_approved".equals(status)) {
            if (!riderHasAcceptedReturnPickup(order)) {
                addActionRow(
                    context,
                    actions,
                    new String[] {"Accept Pickup", "Decline"},
                    new String[] {"#F59E0B", "#DC2626"},
                    new Runnable[] {
                        () -> handler.onStatusUpdate(orderId, "accept_return_pickup", null),
                        () -> handler.onStatusUpdate(orderId, "decline_assignment", null)
                    }
                );
            } else {
                addActionBtn(context, actions, "Scan QR & Pick Up", "#2563EB", () ->
                    handler.onScanReturnQr(orderId));
            }
        }
        if ("return_picked_up".equals(status)) {
            TextView note = new TextView(context);
            note.setText("Waiting for admin to complete refund.");
            note.setTextColor(Color.parseColor("#6B7280"));
            note.setTextSize(12f);
            actions.addView(note);
        }
        if ("return_refund".equals(status)) {
            TextView note = new TextView(context);
            note.setText("Refund completed.");
            note.setTextColor(Color.parseColor("#1565C0"));
            note.setTextSize(12f);
            actions.addView(note);
        }
    }

    public static void applyStatusBadge(
        Context context,
        TextView statusView,
        String status,
        boolean isReturnList,
        JSONObject order
    ) {
        if (statusView == null) {
            return;
        }
        float density = context.getResources().getDisplayMetrics().density;
        int padH = (int) (10 * density);
        int padV = (int) (4 * density);
        statusView.setPadding(padH, padV, padH, padV);
        statusView.setTextColor(Color.WHITE);
        statusView.setTextSize(11f);
        statusView.setTypeface(null, android.graphics.Typeface.BOLD);

        String key = status == null ? "" : status.toLowerCase(Locale.US);
        int bgColor;
        if (isReturnList) {
            if ("return_picked_up".equals(key) || "return_refund".equals(key)) {
                bgColor = Color.parseColor("#059669");
            } else if ("return_approved".equals(key)) {
                bgColor = riderHasAcceptedReturnPickup(order)
                    ? Color.parseColor("#2563EB")
                    : Color.parseColor("#D97706");
            } else {
                bgColor = Color.parseColor("#6B7280");
            }
        } else {
            switch (key) {
                case "to_receive":
                    bgColor = Color.parseColor("#059669");
                    break;
                case "delivered_to_rider":
                case "accepted_by_rider":
                case "ready_for_pickup":
                    bgColor = Color.parseColor("#2563EB");
                    break;
                case "delivered":
                case "completed":
                    bgColor = Color.parseColor("#0F766E");
                    break;
                case "failed_delivery":
                case "cancelled":
                    bgColor = Color.parseColor("#DC2626");
                    break;
                case "to_ship":
                    bgColor = Color.parseColor("#7C3AED");
                    break;
                default:
                    bgColor = Color.parseColor("#4B5563");
                    break;
            }
        }
        GradientDrawable bg = new GradientDrawable();
        bg.setColor(bgColor);
        bg.setCornerRadius(12f * density);
        statusView.setBackground(bg);
    }

    private static void runActionSafely(Context context, Runnable action) {
        if (action == null) {
            return;
        }
        try {
            action.run();
        } catch (Exception e) {
            android.util.Log.e("QuickPuff", "Rider action failed", e);
            toast(context, "Something went wrong. Please try again.");
        }
    }

    private static void addActionRow(
        Context context,
        LinearLayout parent,
        String[] labels,
        String[] colors,
        Runnable[] actions
    ) {
        if (labels == null || labels.length == 0) {
            return;
        }
        float density = context.getResources().getDisplayMetrics().density;
        LinearLayout row = new LinearLayout(context);
        row.setOrientation(LinearLayout.HORIZONTAL);
        int gap = (int) (4 * density);
        for (int i = 0; i < labels.length; i++) {
            TextView btn = createActionChip(context, labels[i],
                colors != null && i < colors.length ? colors[i] : "#0F766E", density);
            final Runnable action = actions != null && i < actions.length ? actions[i] : null;
            btn.setOnClickListener(v -> runActionSafely(context, action));
            LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f);
            if (i > 0) {
                lp.setMarginStart(gap);
            }
            row.addView(btn, lp);
        }
        LinearLayout.LayoutParams rowLp = new LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        rowLp.topMargin = (int) (5 * density);
        parent.addView(row, rowLp);
    }

    private static TextView createActionChip(Context context, String label, String colorHex, float density) {
        TextView btn = new TextView(context);
        btn.setText(label);
        btn.setTextColor(Color.WHITE);
        btn.setTextSize(12f);
        btn.setGravity(Gravity.CENTER);
        btn.setClickable(true);
        btn.setFocusable(true);
        int padH = (int) (6 * density);
        int padV = (int) (8 * density);
        btn.setPadding(padH, padV, padH, padV);
        btn.setMinHeight((int) (36 * density));
        GradientDrawable bg = new GradientDrawable();
        bg.setColor(Color.parseColor(colorHex));
        bg.setCornerRadius(8f * density);
        btn.setBackground(bg);
        return btn;
    }

    private static void addActionBtn(Context context, LinearLayout parent, String label, String colorHex, Runnable action) {
        float density = context.getResources().getDisplayMetrics().density;
        TextView btn = createActionChip(context, label, colorHex, density);
        btn.setTextSize(13f);
        btn.setMinHeight((int) (40 * density));
        int padH = (int) (12 * density);
        int padV = (int) (8 * density);
        btn.setPadding(padH, padV, padH, padV);
        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(
            ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        lp.topMargin = (int) (5 * density);
        btn.setLayoutParams(lp);
        btn.setOnClickListener(v -> runActionSafely(context, action));
        parent.addView(btn);
    }

    public static void toast(Context context, String message) {
        Toast.makeText(context, message, Toast.LENGTH_SHORT).show();
    }
}
