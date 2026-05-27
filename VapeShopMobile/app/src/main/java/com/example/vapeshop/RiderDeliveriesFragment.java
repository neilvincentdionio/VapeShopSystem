package com.example.vapeshop;

public class RiderDeliveriesFragment extends RiderOrderListFragment {

    @Override
    protected String getListTitle() {
        return "My Deliveries";
    }

    @Override
    protected String getListSubtitle() {
        return "Review assigned orders and status. Completed deliveries stay until cleared from Profile.";
    }

    @Override
    protected String getOrdersListType() {
        return "active";
    }

    @Override
    protected boolean isReturnList() {
        return false;
    }

    @Override
    protected String getEmptyMessage() {
        return "No assigned deliveries.";
    }
}
