# Shopee-Like Delivery Process - Testing Guide

## 🧪 **Complete Testing Guide**

### **Prerequisites**
1. Ensure database migration has run successfully
2. Have admin and customer test accounts ready
3. Have some test products in the system

---

## **Phase 1: Database & Setup Testing**

### **1.1 Verify Database Migration**
```bash
cd c:\xampp\htdocs\VapeShopSystem
php spark migrate:status
```
**Expected Result**: Should show the new migration `2026-03-24-120000_AddDeliveryTrackingToRecords` as migrated

### **1.2 Check Database Structure**
```sql
DESCRIBE records;
```
**Expected New Columns**:
- `delivery_status` (ENUM)
- `tracking_number` (VARCHAR)
- `shipped_at` (DATETIME)
- `delivered_at` (DATETIME)
- `shipping_address` (TEXT)
- `contact_number` (VARCHAR)

---

## **Phase 2: Customer Order Flow Testing**

### **2.1 Create Test Order (Customer Account)**
1. **Login as Customer**
   - Navigate to `/login`
   - Use customer credentials

2. **Add Products to Cart**
   - Go to `/customer/products`
   - Add some products to cart
   - Verify age verification if required

3. **Complete Purchase**
   - Go to `/customer/cart`
   - Click "Checkout" or "Direct Order"
   - Complete payment process

### **2.2 Verify Order Creation**
**Expected Result**: 
- Order created with `delivery_status = 'to_pay'`
- Order appears in customer orders page
- Order appears in admin orders page

---

## **Phase 3: Customer Interface Testing**

### **3.1 Test Customer Orders Page**
1. **Navigate to Orders**
   - Go to `/customer/orders`

2. **Test Tab Navigation**
   - Click "All" tab → Should show all orders
   - Click "To Pay" tab → Should show unpaid orders
   - Click "To Ship" tab → Should show paid orders
   - Click "To Receive" tab → Should show shipped orders
   - Click "Completed" tab → Should show completed orders
   - Click "Cancelled" tab → Should show cancelled orders

3. **Verify Status Badges**
   - Each tab should show count of orders
   - Numbers should be accurate

### **3.2 Test Customer Order Actions**

#### **For "To Pay" Orders:**
- **"Pay Now" Button**: 
  - Click should change status to "To Ship"
  - Order should move to "To Ship" tab
- **"Cancel" Button**: 
  - Click should change status to "Cancelled"
  - Order should move to "Cancelled" tab

#### **For "To Receive" Orders:**
- **"Confirm Received" Button**: 
  - Click should change status to "Completed"
  - Order should move to "Completed" tab

#### **For "Completed" Orders:**
- **"Buy Again" Button**: 
  - Click should add items back to cart
  - Should redirect to cart page
- **"Review" Button**: 
  - Click should show "Review feature coming soon" message

---

## **Phase 4: Admin Interface Testing**

### **4.1 Test Admin Orders Page**
1. **Login as Admin**
   - Navigate to `/login`
   - Use admin credentials

2. **Access Orders Management**
   - Go to `/orders`

3. **Verify Display**
   - Should see all orders with delivery status column
   - Status should be color-coded correctly
   - Tracking numbers should display (if assigned)

### **4.2 Test Admin Order Actions**

#### **For Pending Orders:**
- **"Checkout" Button**: 
  - Click should go to checkout page
  - Complete checkout should set status to "completed" and delivery_status to "to_ship"

#### **For "To Ship" Orders:**
- **"Mark as Shipped" Button**: 
  - Click should generate tracking number
  - Should change delivery_status to "to_receive"
  - Should show success message

#### **For "To Receive" Orders:**
- **"Mark as Delivered" Button**: 
  - Click should change delivery_status to "completed"
  - Should show success message

---

## **Phase 5: End-to-End Flow Testing**

### **5.1 Complete Order Lifecycle**
1. **Customer creates order** → Status: "To Pay"
2. **Customer pays** → Status: "To Ship"
3. **Admin marks as shipped** → Status: "To Receive" + Tracking Number
4. **Customer confirms received** → Status: "Completed"

### **5.2 Cancellation Flow**
1. **Customer creates order** → Status: "To Pay"
2. **Customer cancels** → Status: "Cancelled"

### **5.3 Reorder Flow**
1. **Customer has completed order**
2. **Customer clicks "Buy Again"**
3. **Items added to cart**
4. **Customer can checkout again**

---

## **Phase 6: Edge Cases & Error Testing**

### **6.1 Test Invalid Actions**
- Try to pay an already paid order (should show error)
- Try to cancel a shipped order (should show error)
- Try to confirm an unpaid order (should show error)

### **6.2 Test Empty States**
- Customer with no orders → Should show empty state message
- Admin with no orders → Should show empty state message

### **6.3 Test Data Validation**
- Invalid order IDs in URLs → Should show "Order not found"
- Accessing other customer's orders → Should be blocked

---

## **Phase 7: Performance & Responsiveness**

### **7.1 Test Mobile Responsiveness**
- Test customer orders page on mobile screen size
- Test admin orders page on mobile screen size
- Verify tabs are scrollable on small screens

### **7.2 Test AJAX Functionality**
- Admin status updates should work without page reload
- Success/error messages should appear properly

---

## **Phase 8: Data Integrity Testing**

### **8.1 Verify Database Updates**
After each action, check database:
```sql
SELECT id, reference_number, delivery_status, tracking_number, shipped_at, delivered_at 
FROM records 
WHERE record_type = 'sales' 
ORDER BY created_at DESC;
```

### **8.2 Verify Status Transitions**
Ensure status changes follow the correct flow:
- to_pay → to_ship → to_receive → completed
- Any status → cancelled (only for to_pay, to_ship)

---

## **🎯 Success Criteria**

### **Must Pass:**
✅ Customer can view orders in tabbed interface
✅ Customer can pay/cancel orders (when appropriate)
✅ Customer can confirm receipt
✅ Customer can reorder items
✅ Admin can view all orders with delivery status
✅ Admin can update delivery status
✅ Tracking numbers are generated automatically
✅ Status transitions work correctly
✅ Database updates correctly
✅ Error handling works properly

### **Should Pass:**
✅ Mobile responsiveness
✅ AJAX updates work smoothly
✅ Empty states display correctly
✅ Color coding is consistent
✅ Navigation is intuitive

---

## **🐛 Common Issues & Solutions**

### **Issue**: "Whoops! We seem to have hit a snag" error
**Solution**: Check PHP error logs, verify all helper functions are properly defined

### **Issue**: Status badges not showing counts
**Solution**: Verify `getOrderStatusCounts()` method is working correctly

### **Issue**: Admin status updates not working
**Solution**: Check AJAX endpoint and CSRF token

### **Issue**: Tracking numbers not generating
**Solution**: Verify `generateTrackingNumber()` method is called

---

## **📝 Test Checklist**

- [ ] Database migration successful
- [ ] Customer can create orders
- [ ] Customer orders page loads correctly
- [ ] All tabs work and show correct counts
- [ ] Pay button works for "To Pay" orders
- [ ] Cancel button works for "To Pay" orders
- [ ] Confirm button works for "To Receive" orders
- [ ] Buy Again button works for completed orders
- [ ] Admin orders page loads correctly
- [ ] Admin can update delivery status
- [ ] Tracking numbers are generated
- [ ] All status transitions work
- [ ] Error handling works
- [ ] Mobile responsive
- [ ] Database integrity maintained

Run through this checklist systematically to ensure everything works perfectly!
