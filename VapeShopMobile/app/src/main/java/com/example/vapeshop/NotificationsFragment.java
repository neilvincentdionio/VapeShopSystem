package com.example.vapeshop;

import android.graphics.Color;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import org.json.JSONArray;
import org.json.JSONObject;

public class NotificationsFragment extends Fragment {

    private LinearLayout listContainer;
    private TextView statusView;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_notifications, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        MainActivity activity = (MainActivity) requireActivity();
        listContainer = view.findViewById(R.id.notifications_list_container);
        statusView = view.findViewById(R.id.notifications_status);

        view.findViewById(R.id.btn_back_from_notifications).setOnClickListener(v -> navigateBack(activity));
        view.findViewById(R.id.btn_mark_all_notifications).setOnClickListener(v ->
            activity.markAllNotificationsRead(() -> loadNotifications(activity)));

        loadNotifications(activity);
    }

    private void navigateBack(MainActivity activity) {
        activity.navigateBackFromNotifications();
    }

    private void loadNotifications(MainActivity activity) {
        listContainer.removeAllViews();
        showStatus("Loading notifications...", true);
        activity.fetchNotificationsList(new MainActivity.SimpleCallback() {
            @Override
            public void onSuccess(String body) {
                if (!isAdded()) {
                    return;
                }
                try {
                    JSONObject root = new JSONObject(body);
                    if (!root.optBoolean("success", false)) {
                        showStatus(root.optString("message", "Unable to load notifications."), true);
                        return;
                    }
                    JSONObject data = root.optJSONObject("data");
                    JSONArray items = data != null ? data.optJSONArray("notifications") : null;
                    int unread = data != null ? data.optInt("unread_count", 0) : 0;
                    activity.setNotificationUnreadCount(unread);
                    activity.refreshNotificationBadges();
                    renderNotifications(activity, items, unread);
                } catch (Exception e) {
                    showStatus("Invalid notification response.", true);
                }
            }

            @Override
            public void onError(String message) {
                if (isAdded()) {
                    showStatus(message, true);
                }
            }
        });
    }

    private void renderNotifications(MainActivity activity, JSONArray items, int unread) {
        listContainer.removeAllViews();
        if (items == null || items.length() == 0) {
            showStatus("No notifications yet.", true);
            return;
        }
        showStatus(unread > 0
            ? unread + " unread notification" + (unread == 1 ? "" : "s")
            : "All caught up.", true);

        for (int i = 0; i < items.length(); i++) {
            JSONObject item = items.optJSONObject(i);
            if (item == null) {
                continue;
            }
            View row = LayoutInflater.from(requireContext())
                .inflate(R.layout.item_notification_row, listContainer, false);
            boolean isRead = item.optBoolean("is_read", false);
            TextView title = row.findViewById(R.id.notification_title);
            TextView message = row.findViewById(R.id.notification_message);
            TextView time = row.findViewById(R.id.notification_time);
            View dot = row.findViewById(R.id.notification_unread_dot);
            title.setText(item.optString("title", "Notification"));
            message.setText(item.optString("message", ""));
            time.setText(item.optString("created_label", ""));
            dot.setVisibility(isRead ? View.GONE : View.VISIBLE);
            if (!isRead) {
                row.setBackgroundColor(Color.parseColor("#F0FDFA"));
            }
            row.setOnClickListener(v -> onNotificationClick(activity, item));
            listContainer.addView(row);
        }
    }

    private void onNotificationClick(MainActivity activity, JSONObject item) {
        int id = item.optInt("id", 0);
        if (id > 0 && !item.optBoolean("is_read", false)) {
            activity.markNotificationRead(id, () -> {
                // continue navigation after mark read
            });
        }
        activity.handleNotificationNavigation(item);
        loadNotifications(activity);
    }

    private void showStatus(String text, boolean visible) {
        if (statusView == null) {
            return;
        }
        statusView.setText(text);
        statusView.setVisibility(visible && text != null && !text.isEmpty()
            ? View.VISIBLE
            : View.GONE);
    }
}
