package com.example.vapeshop;

public class RiderReturnsFragment extends RiderOrderListFragment {

    @Override
    protected String getListTitle() {
        return "Return Pickups";
    }

    @Override
    protected String getListSubtitle() {
        return "Accept pickup, scan QR, then complete. Clear completed items from Profile.";
    }

    @Override
    protected String getOrdersListType() {
        return "returns";
    }

    @Override
    protected boolean isReturnList() {
        return true;
    }

    @Override
    protected String getEmptyMessage() {
        return "No return pickups assigned to you right now.";
    }
}
