package com.example.vapeshop;

import android.annotation.SuppressLint;
import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.net.Uri;
import android.os.Bundle;
import android.graphics.Bitmap;
import android.graphics.Color;
import android.webkit.GeolocationPermissions;
import android.webkit.JavascriptInterface;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.graphics.Typeface;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RatingBar;
import android.widget.Spinner;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Switch;
import android.widget.TextView;
import android.widget.Toast;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.os.Handler;
import android.os.Looper;
import android.net.Uri;
import android.provider.OpenableColumns;
import android.util.Patterns;
import android.text.InputType;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.app.AppCompatDelegate;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.fragment.app.Fragment;
import androidx.fragment.app.FragmentManager;
import androidx.fragment.app.FragmentTransaction;

import org.json.JSONArray;
import org.json.JSONObject;
import com.google.zxing.BarcodeFormat;
import com.google.zxing.MultiFormatWriter;
import com.google.zxing.common.BitMatrix;

import java.io.BufferedReader;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collections;
import java.util.Comparator;
import java.util.HashMap;
import java.util.HashSet;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Set;
import java.util.TreeSet;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

public class MainActivity extends AppCompatActivity {
    private static final String PREFS_NAME = "vapeshop_auth";
    private static final String KEY_USER_FULL_NAME = "user_full_name";
    private static final String KEY_USER_EMAIL = "user_email";
    private static final String KEY_USER_PASSWORD = "user_password";
    private static final String KEY_USER_PHONE = "user_phone";
    private static final String KEY_USER_STREET = "user_street";
    private static final String KEY_USER_CITY = "user_city";
    private static final String KEY_USER_BARANGAY = "user_barangay";
    private static final String KEY_USER_POSTAL_CODE = "user_postal_code";
    private static final String KEY_USER_PROVINCE = "user_province";
    private static final String KEY_USER_COUNTRY = "user_country";
    private static final String KEY_USER_LAT = "user_lat";
    private static final String KEY_USER_LNG = "user_lng";
    private static final String KEY_NOTIFICATIONS_ENABLED = "notifications_enabled";
    private static final String KEY_DARK_MODE = "dark_mode";
    private static final String KEY_USER_ROLE = "user_role";
    private static final String KEY_USER_ID = "user_id";
    private static final String[] API_BASE_URLS = {
        // Physical device via USB + adb reverse (run: adb reverse tcp:8080 tcp:80)
        "http://127.0.0.1:8080/VapeShopSystem/mobile_api/",
        // Android Studio emulator
        "http://10.0.2.2/VapeShopSystem/mobile_api/",
        // Genymotion emulator
        "http://10.0.3.2/VapeShopSystem/mobile_api/",
        // This PC LAN IPv4(s) for physical device on same network/hotspot
        "http://192.168.137.94/VapeShopSystem/mobile_api/",
        "http://192.168.1.72/VapeShopSystem/mobile_api/"
    };
    private boolean isLoggedIn = false;
    private String currentUserRole = "customer";
    private int currentUserId = 0;
    private int pendingProofOrderId = 0;
    private final ActivityResultLauncher<String> deliveryProofPickerLauncher =
        registerForActivityResult(new ActivityResultContracts.GetContent(), uri -> {
            if (uri != null && pendingProofOrderId > 0) {
                submitRiderProofToServer(pendingProofOrderId, uri, new SimpleCallback() {
                    @Override
                    public void onSuccess(String message) {
                        Toast.makeText(MainActivity.this, message, Toast.LENGTH_SHORT).show();
                        loadFragment(new RiderDeliveriesFragment());
                    }

                    @Override
                    public void onError(String message) {
                        Toast.makeText(MainActivity.this, message, Toast.LENGTH_LONG).show();
                    }
                });
            }
            pendingProofOrderId = 0;
        });
    private final LinkedHashMap<String, CartItem> cartItems = new LinkedHashMap<>();
    private final Map<Integer, ProductCatalogEntry> productCatalogById = new HashMap<>();
    private final Map<String, Integer> productIdByName = new HashMap<>();
    private final ExecutorService networkExecutor = Executors.newSingleThreadExecutor();
    private final Handler mainHandler = new Handler(Looper.getMainLooper());
    private static final long MESSAGE_AUTO_REFRESH_MS = 4000L;
    private static final long MESSAGE_BADGE_POLL_MS = 12000L;
    private int supportUnreadCount = 0;
    private boolean messageBadgePollingActive = false;
    private final Runnable messageBadgePollRunnable = new Runnable() {
        @Override
        public void run() {
            pollSupportUnreadCount();
            if (messageBadgePollingActive) {
                mainHandler.postDelayed(this, MESSAGE_BADGE_POLL_MS);
            }
        }
    };
    private AttachmentPickCallback pendingAttachmentPickCallback;
    private final ActivityResultLauncher<String[]> refundAttachmentPickerLauncher =
        registerForActivityResult(new ActivityResultContracts.OpenMultipleDocuments(), uris -> {
            if (pendingAttachmentPickCallback != null) {
                pendingAttachmentPickCallback.onPicked(uris == null ? new ArrayList<>() : uris);
                pendingAttachmentPickCallback = null;
            }
        });

    private interface AttachmentPickCallback {
        void onPicked(List<Uri> uris);
    }

    private static class CartItem {
        String cartKey;
        int productId;
        Integer variantId;
        String name;
        String details;
        double price;
        int imageResId;
        int quantity;

        CartItem(String cartKey, int productId, Integer variantId, String name, String details, double price, int imageResId, int quantity) {
            this.cartKey = cartKey;
            this.productId = productId;
            this.variantId = variantId;
            this.name = name;
            this.details = details;
            this.price = price;
            this.imageResId = imageResId;
            this.quantity = quantity;
        }
    }

    static class ProductVariant {
        int id;
        String flavor;
        int puffs;
        double price;
        int stockQty;

        ProductVariant(int id, String flavor, int puffs, double price, int stockQty) {
            this.id = id;
            this.flavor = flavor;
            this.puffs = puffs;
            this.price = price;
            this.stockQty = stockQty;
        }

        int resolvePuffs(int productPuffs) {
            return puffs > 0 ? puffs : productPuffs;
        }
    }

    static class ProductCatalogEntry {
        int id;
        String name;
        String category;
        String spec;
        int puffs;
        double price;
        int stockQty;
        double averageRating;
        int reviewCount;
        boolean hasFlavors;
        List<ProductVariant> variants;

        ProductCatalogEntry(
            int id,
            String name,
            String category,
            String spec,
            int puffs,
            double price,
            int stockQty,
            double averageRating,
            int reviewCount,
            boolean hasFlavors,
            List<ProductVariant> variants
        ) {
            this.id = id;
            this.name = name;
            this.category = category;
            this.spec = spec;
            this.puffs = puffs;
            this.price = price;
            this.stockQty = stockQty;
            this.averageRating = averageRating;
            this.reviewCount = reviewCount;
            this.hasFlavors = hasFlavors;
            this.variants = variants;
        }

        boolean needsFlavorSelection() {
            if (!hasFlavors || variants == null || variants.isEmpty()) {
                return false;
            }
            for (ProductVariant variant : variants) {
                if (variant.stockQty > 0 && variant.flavor != null && !variant.flavor.trim().isEmpty()) {
                    return true;
                }
            }
            return false;
        }

        List<ProductVariant> getAvailableVariants() {
            List<ProductVariant> available = new ArrayList<>();
            if (variants == null) {
                return available;
            }
            for (ProductVariant variant : variants) {
                if (variant.stockQty > 0) {
                    available.add(variant);
                }
            }
            return available;
        }

        String formatRatingLabel() {
            if (reviewCount <= 0 || averageRating <= 0) {
                return "No ratings yet";
            }
            return String.format(Locale.US, "%.1f (%d review%s)", averageRating, reviewCount, reviewCount == 1 ? "" : "s");
        }

        String formatStockLabel() {
            if (needsFlavorSelection()) {
                int flavorCount = variants == null ? 0 : variants.size();
                return String.format(Locale.US, "In stock (%d available • %d flavors)", stockQty, flavorCount);
            }
            if (stockQty <= 0) {
                return "Out of stock";
            }
            return String.format(Locale.US, "In stock (%d available)", stockQty);
        }

        String formatFlavorSummary() {
            if (variants == null || variants.isEmpty()) {
                return "No flavors listed";
            }
            StringBuilder summary = new StringBuilder();
            int shown = 0;
            for (ProductVariant variant : variants) {
                if (shown >= 8) {
                    summary.append("\n+").append(variants.size() - shown).append(" more flavors");
                    break;
                }
                if (summary.length() > 0) {
                    summary.append('\n');
                }
                summary.append(variant.flavor)
                    .append(" (")
                    .append(variant.stockQty)
                    .append(" left)");
                shown++;
            }
            return summary.toString();
        }
    }

    private static class OrderInfo {
        int orderId;
        String referenceNumber;
        String orderDate;
        String deliveryStatus;
        String itemsSummary;
        double totalAmount;
        String shippingAddress;
        String shippingContact;
        boolean reviewSubmitted;
        boolean refundRequested;
        boolean canRequestReturn;
        boolean canPay;
        boolean canCancel;
        boolean canConfirmReceived;
        String paymentStatus;
        String refundReason;
        String refundMethod;
        String refundAccount;
        String returnCode;
        String qrPayload;

        OrderInfo(
            int orderId,
            String referenceNumber,
            String orderDate,
            String deliveryStatus,
            String itemsSummary,
            double totalAmount,
            String shippingAddress,
            String shippingContact
        ) {
            this.orderId = orderId;
            this.referenceNumber = referenceNumber;
            this.orderDate = orderDate;
            this.deliveryStatus = deliveryStatus;
            this.itemsSummary = itemsSummary;
            this.totalAmount = totalAmount;
            this.shippingAddress = shippingAddress;
            this.shippingContact = shippingContact;
            this.reviewSubmitted = false;
            this.refundRequested = false;
            this.canRequestReturn = false;
            this.canPay = false;
            this.canCancel = false;
            this.canConfirmReceived = false;
            this.paymentStatus = "unpaid";
            this.refundReason = "";
            this.refundMethod = "gcash";
            this.refundAccount = "";
            this.returnCode = "";
            this.qrPayload = "";
        }
    }

    private static class SupportMessage {
        int id;
        String senderName;
        String messageBody;
        String createdAt;
        String senderRole;
        String messageType;
        boolean fromCustomer;

        SupportMessage(
            int id,
            String senderName,
            String messageBody,
            String createdAt,
            String senderRole,
            String messageType,
            boolean fromCustomer
        ) {
            this.id = id;
            this.senderName = senderName;
            this.messageBody = messageBody;
            this.createdAt = createdAt;
            this.senderRole = senderRole == null ? "" : senderRole;
            this.messageType = messageType == null ? "text" : messageType;
            this.fromCustomer = fromCustomer;
        }
    }

    private static class ChatOrderOption {
        int orderId;
        String reference;
        String productSummary;

        ChatOrderOption(int orderId, String reference, String productSummary) {
            this.orderId = orderId;
            this.reference = reference;
            this.productSummary = productSummary;
        }

        @Override
        public String toString() {
            if (reference == null || reference.trim().isEmpty()) {
                return "No specific order";
            }
            return reference + " - " + (productSummary == null ? "" : productSummary);
        }
    }

    private interface AuthCallback {
        void onSuccess(String fullName, String email);
        void onError(String message);
    }

    private interface SimpleCallback {
        void onSuccess(String message);
        void onError(String message);
    }

    private interface ProductsCallback {
        void onSuccess(Map<String, ProductCatalogEntry> productsByName);
        void onError(String message);
    }

    private interface ProductDetailCallback {
        void onSuccess(ProductCatalogEntry product);
        void onError(String message);
    }

    private interface CartLoadCallback {
        void onSuccess();
        void onError(String message);
    }

    private interface OrdersCallback {
        void onSuccess(List<OrderInfo> orders);
        void onError(String message);
    }

    private interface MessagesCallback {
        void onSuccess(List<SupportMessage> messages);
        void onError(String message);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        applySavedDarkMode();
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setupNavigation();
        restoreSessionIfPossible();
    }

    private void restoreSessionIfPossible() {
        if (hasRegisteredAccount()) {
            SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
            currentUserRole = prefs.getString(KEY_USER_ROLE, "customer");
            currentUserId = prefs.getInt(KEY_USER_ID, 0);
            isLoggedIn = true;
            routeAfterLogin();
            return;
        }
        loadFragment(new LoginFragment());
    }

    @Override
    protected void onDestroy() {
        stopMessageBadgePolling();
        super.onDestroy();
        networkExecutor.shutdownNow();
    }

    private void setupNavigation() {
        findViewById(R.id.nav_home).setOnClickListener(v ->
            loadFragment(new HomeFragment()));

        findViewById(R.id.nav_cart).setOnClickListener(v ->
            loadFragment(new CartFragment()));

        findViewById(R.id.nav_messages).setOnClickListener(v -> {
            if (!isUserLoggedIn()) {
                Toast.makeText(this, "Please login to open messages", Toast.LENGTH_SHORT).show();
                loadFragment(new LoginFragment());
                return;
            }
            loadFragment(MessagesFragment.newInstance("", "", false));
        });

        findViewById(R.id.nav_my_purchase).setOnClickListener(v -> {
            if (!isUserLoggedIn()) {
                Toast.makeText(this, "Please login to view your purchases", Toast.LENGTH_SHORT).show();
                loadFragment(new LoginFragment());
                return;
            }
            loadFragment(new MyPurchaseFragment());
        });

        findViewById(R.id.nav_settings).setOnClickListener(v ->
            loadFragment(new SettingsFragment()));
    }

    private void loadFragment(Fragment fragment) {
        FragmentManager fragmentManager = getSupportFragmentManager();
        FragmentTransaction fragmentTransaction = fragmentManager.beginTransaction();
        fragmentTransaction.replace(R.id.fragment_container, fragment);
        fragmentTransaction.commit();
    }

    public void openRefundAttachmentPicker(AttachmentPickCallback callback) {
        pendingAttachmentPickCallback = callback;
        refundAttachmentPickerLauncher.launch(new String[]{"image/*", "video/*"});
    }

    private void onLoginSuccess() {
        isLoggedIn = true;
        routeAfterLogin();
        Toast.makeText(this, "Login successful", Toast.LENGTH_SHORT).show();
    }

    public void routeAfterLogin() {
        setRoleNavigationVisible(isCustomerRole());
        if (isCustomerRole()) {
            startMessageBadgePolling();
        } else {
            stopMessageBadgePolling();
            supportUnreadCount = 0;
            refreshMessageBadges();
        }
        if (isAdminRole()) {
            loadFragment(new AdminOrdersFragment());
        } else if (isRiderRole()) {
            loadFragment(new RiderDeliveriesFragment());
        } else {
            loadFragment(new HomeFragment());
        }
    }

    public boolean isCustomerRole() {
        return "customer".equalsIgnoreCase(currentUserRole) || currentUserRole.isEmpty();
    }

    public boolean isAdminRole() {
        String role = currentUserRole == null ? "" : currentUserRole.toLowerCase(Locale.US);
        return "admin".equals(role) || "staff".equals(role);
    }

    public boolean isRiderRole() {
        return "rider".equalsIgnoreCase(currentUserRole);
    }

    private void setRoleNavigationVisible(boolean visible) {
        int vis = visible ? View.VISIBLE : View.GONE;
        findViewById(R.id.nav_home).setVisibility(vis);
        findViewById(R.id.nav_cart).setVisibility(vis);
        findViewById(R.id.nav_messages_wrap).setVisibility(vis);
        findViewById(R.id.nav_my_purchase).setVisibility(vis);
    }

    public int getCurrentUserId() {
        return currentUserId;
    }

    public String getCurrentUserRole() {
        return currentUserRole;
    }

    public boolean isUserLoggedIn() {
        return isLoggedIn;
    }

    public String getRegisteredEmail() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        return prefs.getString(KEY_USER_EMAIL, "");
    }

    public String getRegisteredFullName() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        return prefs.getString(KEY_USER_FULL_NAME, "Customer");
    }

    public String getRegisteredPhone() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        return prefs.getString(KEY_USER_PHONE, "");
    }

    public String getRegisteredShippingAddress() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        String street = prefs.getString(KEY_USER_STREET, "");
        String barangay = prefs.getString(KEY_USER_BARANGAY, "");
        String city = prefs.getString(KEY_USER_CITY, "");
        String province = prefs.getString(KEY_USER_PROVINCE, "South Cotabato");
        String postalCode = prefs.getString(KEY_USER_POSTAL_CODE, "");
        String country = prefs.getString(KEY_USER_COUNTRY, "Philippines");
        StringBuilder sb = new StringBuilder();
        if (!street.trim().isEmpty()) sb.append(street.trim());
        if (!barangay.trim().isEmpty()) {
            if (sb.length() > 0) sb.append(", ");
            sb.append(barangay.trim());
        }
        if (!city.trim().isEmpty()) {
            if (sb.length() > 0) sb.append(", ");
            sb.append(city.trim());
        }
        if (!province.trim().isEmpty()) {
            if (sb.length() > 0) sb.append(", ");
            sb.append(province.trim());
        }
        if (!postalCode.trim().isEmpty()) {
            if (sb.length() > 0) sb.append(", ");
            sb.append(postalCode.trim());
        }
        if (!country.trim().isEmpty()) {
            if (sb.length() > 0) sb.append(", ");
            sb.append(country.trim());
        }
        return sb.toString();
    }

    public double getRegisteredLatitude() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        return parseDoubleSafe(prefs.getString(KEY_USER_LAT, "6.1164"), 6.1164);
    }

    public double getRegisteredLongitude() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        return parseDoubleSafe(prefs.getString(KEY_USER_LNG, "125.1716"), 125.1716);
    }

    public boolean areNotificationsEnabled() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        return prefs.getBoolean(KEY_NOTIFICATIONS_ENABLED, true);
    }

    public void setNotificationsEnabled(boolean enabled) {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        prefs.edit().putBoolean(KEY_NOTIFICATIONS_ENABLED, enabled).apply();
    }

    public boolean isDarkModeEnabled() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        return prefs.getBoolean(KEY_DARK_MODE, false);
    }

    public void setDarkModeEnabled(boolean enabled) {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        prefs.edit().putBoolean(KEY_DARK_MODE, enabled).apply();
        AppCompatDelegate.setDefaultNightMode(
            enabled ? AppCompatDelegate.MODE_NIGHT_YES : AppCompatDelegate.MODE_NIGHT_NO
        );
    }

    public boolean updateProfile(String fullName, String email) {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        prefs.edit()
            .putString(KEY_USER_FULL_NAME, fullName)
            .putString(KEY_USER_EMAIL, email)
            .apply();
        return true;
    }

    public boolean updatePassword(String currentPassword, String newPassword) {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        String savedPassword = prefs.getString(KEY_USER_PASSWORD, "");
        if (!savedPassword.equals(currentPassword)) {
            return false;
        }
        prefs.edit().putString(KEY_USER_PASSWORD, newPassword).apply();
        return true;
    }

    private void applySavedDarkMode() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        boolean isDarkMode = prefs.getBoolean(KEY_DARK_MODE, false);
        AppCompatDelegate.setDefaultNightMode(
            isDarkMode ? AppCompatDelegate.MODE_NIGHT_YES : AppCompatDelegate.MODE_NIGHT_NO
        );
    }

    private void saveAccountLocally(
        String fullName,
        String email,
        String password,
        String phone,
        String street,
        String city,
        String barangay,
        String postalCode,
        String province,
        String country,
        double latitude,
        double longitude
    ) {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        prefs.edit()
            .putString(KEY_USER_FULL_NAME, fullName)
            .putString(KEY_USER_EMAIL, email)
            .putString(KEY_USER_PASSWORD, password)
            .putString(KEY_USER_PHONE, phone == null ? "" : phone)
            .putString(KEY_USER_STREET, street == null ? "" : street)
            .putString(KEY_USER_CITY, city == null ? "" : city)
            .putString(KEY_USER_BARANGAY, barangay == null ? "" : barangay)
            .putString(KEY_USER_POSTAL_CODE, postalCode == null ? "" : postalCode)
            .putString(KEY_USER_PROVINCE, province == null ? "South Cotabato" : province)
            .putString(KEY_USER_COUNTRY, country == null ? "Philippines" : country)
            .putString(KEY_USER_LAT, String.format(Locale.US, "%.6f", latitude))
            .putString(KEY_USER_LNG, String.format(Locale.US, "%.6f", longitude))
            .apply();
    }

    public void loginWithServer(String email, String password, AuthCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", email);
        params.put("password", password);
        apiPost("login.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Invalid email or password"));
                        return;
                    }
                    JSONObject data = root.optJSONObject("data");
                    String fullName = data != null ? data.optString("full_name", "Customer") : "Customer";
                    String savedEmail = data != null ? data.optString("email", email) : email;
                    String phone = data != null ? data.optString("phone", "") : "";
                    String street = data != null ? data.optString("street", "") : "";
                    String city = data != null ? data.optString("city", "") : "";
                    String barangay = data != null ? data.optString("barangay", "") : "";
                    String postalCode = data != null ? data.optString("postal_code", "") : "";
                    String province = data != null ? data.optString("province", "South Cotabato") : "South Cotabato";
                    String country = data != null ? data.optString("country", "Philippines") : "Philippines";
                    double latitude = data != null ? data.optDouble("latitude", 6.1164) : 6.1164;
                    double longitude = data != null ? data.optDouble("longitude", 125.1716) : 125.1716;
                    String role = data != null ? data.optString("role", "customer") : "customer";
                    int userId = data != null ? data.optInt("user_id", 0) : 0;
                    currentUserRole = role == null || role.isEmpty() ? "customer" : role;
                    currentUserId = userId;
                    saveAccountLocally(
                        fullName, savedEmail, password, phone, street, city, barangay, postalCode, province, country, latitude, longitude
                    );
                    getSharedPreferences(PREFS_NAME, MODE_PRIVATE).edit()
                        .putString(KEY_USER_ROLE, currentUserRole)
                        .putInt(KEY_USER_ID, currentUserId)
                        .apply();
                    callback.onSuccess(fullName, savedEmail);
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void registerWithServer(
        String fullName,
        String email,
        String password,
        String phone,
        String street,
        String city,
        String barangay,
        String postalCode,
        double deliveryLatitude,
        double deliveryLongitude,
        SimpleCallback callback
    ) {
        Map<String, String> params = new HashMap<>();
        params.put("full_name", fullName);
        params.put("email", email);
        params.put("password", password);
        params.put("phone", phone);
        params.put("street", street);
        params.put("city", city);
        params.put("barangay", barangay);
        params.put("postal_code", postalCode);
        params.put("province", "South Cotabato");
        params.put("country", "Philippines");
        params.put("delivery_latitude", String.format(Locale.US, "%.6f", deliveryLatitude));
        params.put("delivery_longitude", String.format(Locale.US, "%.6f", deliveryLongitude));

        apiPost("register.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Account already exists"));
                        return;
                    }
                    saveAccountLocally(
                        fullName,
                        email,
                        password,
                        phone,
                        street,
                        city,
                        barangay,
                        postalCode,
                        "South Cotabato",
                        "Philippines",
                        deliveryLatitude,
                        deliveryLongitude
                    );
                    callback.onSuccess(root.optString("message", "Account created"));
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void updateProfileWithServer(String fullName, String email, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("current_email", getRegisteredEmail());
        params.put("full_name", fullName);
        params.put("email", email);
        apiPost("profile_update.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Unable to update profile"));
                        return;
                    }
                    updateProfile(fullName, email);
                    callback.onSuccess(root.optString("message", "Profile updated"));
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void updatePasswordWithServer(String currentPassword, String newPassword, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("current_password", currentPassword);
        params.put("new_password", newPassword);
        apiPost("password_update.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Current password is incorrect"));
                        return;
                    }
                    updatePassword(currentPassword, newPassword);
                    callback.onSuccess(root.optString("message", "Password updated"));
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void syncCartItemToServer(String productName, int quantity, Integer variantId, SimpleCallback callback) {
        if (!isUserLoggedIn()) {
            callback.onError("Please login first before adding to cart");
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("product_name", productName);
        params.put("quantity", String.valueOf(quantity));
        if (variantId != null && variantId > 0) {
            params.put("variant_id", String.valueOf(variantId));
        }
        apiPost("cart_add.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Unable to add item to cart"));
                        return;
                    }
                    callback.onSuccess(root.optString("message", "Product added to cart."));
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }
            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public ProductCatalogEntry getProductByName(String productName) {
        Integer productId = productIdByName.get(productName.toUpperCase(Locale.US));
        if (productId == null) {
            return null;
        }
        return productCatalogById.get(productId);
    }

    public ProductCatalogEntry getProductById(int productId) {
        return productCatalogById.get(productId);
    }

    private String buildCartKey(int productId, Integer variantId) {
        return productId + "::" + (variantId != null && variantId > 0 ? variantId : 0);
    }

    private void cacheProductCatalogEntry(ProductCatalogEntry entry) {
        productCatalogById.put(entry.id, entry);
        productIdByName.put(entry.name.toUpperCase(Locale.US), entry.id);
    }

    public void fetchProductDetailFromServer(int productId, ProductDetailCallback callback) {
        apiGet("products.php?product_id=" + productId, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Unable to fetch product"));
                        return;
                    }
                    JSONObject data = root.optJSONObject("data");
                    JSONObject item = data != null ? data.optJSONObject("product") : null;
                    ProductCatalogEntry entry = parseProductEntry(item);
                    if (entry == null) {
                        callback.onError("Product not found");
                        return;
                    }
                    cacheProductCatalogEntry(entry);
                    callback.onSuccess(entry);
                } catch (Exception e) {
                    callback.onError("Invalid product response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    private ProductCatalogEntry parseProductEntry(JSONObject item) {
        if (item == null) {
            return null;
        }
        String name = item.optString("name", "").trim();
        if (name.isEmpty()) {
            return null;
        }
        List<ProductVariant> variants = new ArrayList<>();
        JSONArray variantsArray = item.optJSONArray("variants");
        if (variantsArray != null) {
            for (int i = 0; i < variantsArray.length(); i++) {
                JSONObject variantJson = variantsArray.optJSONObject(i);
                if (variantJson == null) {
                    continue;
                }
                String flavor = variantJson.optString("flavor", "").trim();
                if (flavor.isEmpty()) {
                    continue;
                }
                variants.add(new ProductVariant(
                    variantJson.optInt("id", 0),
                    flavor,
                    variantJson.has("puffs") && !variantJson.isNull("puffs") ? variantJson.optInt("puffs", 0) : 0,
                    variantJson.optDouble("price", item.optDouble("price", 0)),
                    variantJson.optInt("stock_qty", 0)
                ));
            }
        }
        return new ProductCatalogEntry(
            item.optInt("id", 0),
            name,
            item.optString("category", ""),
            item.optString("spec", ""),
            item.optInt("puffs", 0),
            item.optDouble("price", 0),
            item.optInt("stock_qty", 0),
            item.optDouble("average_rating", 0),
            item.optInt("review_count", 0),
            item.optBoolean("has_flavors", false),
            variants
        );
    }

    public void showAddToCartFlow(Fragment host, ProductCatalogEntry product) {
        if (product == null) {
            Toast.makeText(this, "Product unavailable", Toast.LENGTH_SHORT).show();
            return;
        }
        if (!isUserLoggedIn()) {
            Toast.makeText(this, "Please login first before adding to cart", Toast.LENGTH_SHORT).show();
            loadFragment(new LoginFragment());
            return;
        }
        if (product.stockQty <= 0) {
            Toast.makeText(this, "This product is out of stock", Toast.LENGTH_SHORT).show();
            return;
        }
        if (product.needsFlavorSelection()) {
            showFlavorSelectionDialog(host, product);
            return;
        }
        syncCartItemToServer(product.name, 1, null, new SimpleCallback() {
            @Override
            public void onSuccess(String message) {
                addProductToCart(product, null);
                Toast.makeText(MainActivity.this, message, Toast.LENGTH_SHORT).show();
            }

            @Override
            public void onError(String message) {
                Toast.makeText(MainActivity.this, message, Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void showFlavorSelectionDialog(Fragment host, ProductCatalogEntry product) {
        View dialogView = LayoutInflater.from(this).inflate(R.layout.dialog_select_flavor, null);
        TextView title = dialogView.findViewById(R.id.dialog_product_name);
        TextView subtitle = dialogView.findViewById(R.id.dialog_product_subtitle);
        TextView puffLabel = dialogView.findViewById(R.id.dialog_puff_label);
        Spinner puffSpinner = dialogView.findViewById(R.id.dialog_puff_spinner);
        Spinner flavorSpinner = dialogView.findViewById(R.id.dialog_flavor_spinner);
        TextView stockInfo = dialogView.findViewById(R.id.dialog_flavor_stock);

        title.setText(product.name);
        subtitle.setText("Choose puff and flavor before adding this product to cart.");

        List<Integer> puffOptions = getPuffOptions(product);
        boolean showPuffSelector = !puffOptions.isEmpty();
        puffLabel.setVisibility(showPuffSelector ? View.VISIBLE : View.GONE);
        puffSpinner.setVisibility(showPuffSelector ? View.VISIBLE : View.GONE);

        final List<ProductVariant>[] filteredVariants = new List[]{product.getAvailableVariants()};
        if (showPuffSelector) {
            filteredVariants[0] = filterVariantsByPuff(product, puffOptions.get(0));
        }
        if (filteredVariants[0].isEmpty()) {
            Toast.makeText(this, "No available flavors right now", Toast.LENGTH_SHORT).show();
            return;
        }

        Runnable refreshFlavorSpinner = () -> {
            List<String> labels = new ArrayList<>();
            for (ProductVariant variant : filteredVariants[0]) {
                labels.add(variant.flavor + " (" + variant.stockQty + " left)");
            }
            ArrayAdapter<String> flavorAdapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, labels);
            flavorAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
            flavorSpinner.setAdapter(flavorAdapter);
            updateFlavorStockLabel(stockInfo, filteredVariants[0].get(0));
        };

        if (showPuffSelector) {
            List<String> puffLabels = new ArrayList<>();
            for (Integer puff : puffOptions) {
                puffLabels.add(String.format(Locale.US, "%,d Puffs", puff));
            }
            ArrayAdapter<String> puffAdapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, puffLabels);
            puffAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
            puffSpinner.setAdapter(puffAdapter);

            puffSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
                @Override
                public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                    int selectedPuff = puffOptions.get(position);
                    filteredVariants[0] = filterVariantsByPuff(product, selectedPuff);
                    if (filteredVariants[0].isEmpty()) {
                        stockInfo.setText("No flavors available for this puff option");
                    } else {
                        refreshFlavorSpinner.run();
                    }
                }

                @Override
                public void onNothingSelected(AdapterView<?> parent) {}
            });
        }

        flavorSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                if (position >= 0 && position < filteredVariants[0].size()) {
                    updateFlavorStockLabel(stockInfo, filteredVariants[0].get(position));
                }
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });

        refreshFlavorSpinner.run();

        AlertDialog dialog = new AlertDialog.Builder(this)
            .setView(dialogView)
            .setNegativeButton("Cancel", null)
            .setPositiveButton("Add to Cart", null)
            .create();

        dialog.setOnShowListener(d -> dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(v -> {
            if (filteredVariants[0].isEmpty()) {
                Toast.makeText(this, "Please select an available flavor", Toast.LENGTH_SHORT).show();
                return;
            }
            int flavorIndex = flavorSpinner.getSelectedItemPosition();
            if (flavorIndex < 0 || flavorIndex >= filteredVariants[0].size()) {
                Toast.makeText(this, "Please select a flavor", Toast.LENGTH_SHORT).show();
                return;
            }
            ProductVariant selectedVariant = filteredVariants[0].get(flavorIndex);
            syncCartItemToServer(product.name, 1, selectedVariant.id, new SimpleCallback() {
                @Override
                public void onSuccess(String message) {
                    addProductToCart(product, selectedVariant);
                    Toast.makeText(MainActivity.this, message, Toast.LENGTH_SHORT).show();
                    dialog.dismiss();
                }

                @Override
                public void onError(String message) {
                    Toast.makeText(MainActivity.this, message, Toast.LENGTH_SHORT).show();
                }
            });
        }));
        dialog.show();
        if (dialog.getWindow() != null) {
            int margin = Math.round(getResources().getDisplayMetrics().density * 16f);
            int width = getResources().getDisplayMetrics().widthPixels - (margin * 2);
            dialog.getWindow().setLayout(width, ViewGroup.LayoutParams.WRAP_CONTENT);
        }
    }

    private void updateFlavorStockLabel(TextView stockInfo, ProductVariant variant) {
        stockInfo.setText(variant.stockQty + " left in stock");
    }

    private List<Integer> getPuffOptions(ProductCatalogEntry product) {
        TreeSet<Integer> options = new TreeSet<>();
        if (product.puffs > 0) {
            options.add(product.puffs);
        }
        if (product.variants != null) {
            for (ProductVariant variant : product.variants) {
                int resolved = variant.resolvePuffs(product.puffs);
                if (resolved > 0) {
                    options.add(resolved);
                }
            }
        }
        return new ArrayList<>(options);
    }

    private List<ProductVariant> filterVariantsByPuff(ProductCatalogEntry product, int selectedPuff) {
        List<ProductVariant> filtered = new ArrayList<>();
        for (ProductVariant variant : product.getAvailableVariants()) {
            if (variant.resolvePuffs(product.puffs) == selectedPuff) {
                filtered.add(variant);
            }
        }
        return filtered;
    }

    public void addProductToCart(ProductCatalogEntry product, ProductVariant variant) {
        Integer variantId = variant != null ? variant.id : null;
        String cartKey = buildCartKey(product.id, variantId);
        String details = buildCartDetails(product, variant);
        double unitPrice = variant != null && variant.price > 0 ? variant.price : product.price;
        CartItem item = cartItems.get(cartKey);
        if (item == null) {
            cartItems.put(cartKey, new CartItem(
                cartKey,
                product.id,
                variantId,
                product.name,
                details,
                unitPrice,
                getImageResForProductName(product.name),
                1
            ));
            return;
        }
        item.quantity++;
    }

    private String buildCartDetails(ProductCatalogEntry product, ProductVariant variant) {
        StringBuilder details = new StringBuilder(product.spec == null ? "" : product.spec);
        if (variant != null) {
            int puffValue = variant.resolvePuffs(product.puffs);
            if (puffValue > 0) {
                if (details.length() > 0) {
                    details.append(" • ");
                }
                details.append(String.format(Locale.US, "%,d Puffs", puffValue));
            }
            if (details.length() > 0) {
                details.append(" • ");
            }
            details.append(variant.flavor);
        }
        return details.toString();
    }

    private static final int REQ_CHECKOUT_LOCATION = 9021;
    private static final String GCASH_MERCHANT_NUMBER = "+639365879409";
    private static final String GCASH_MERCHANT_NAME = "QuickPuff VapeShop";
    private final Map<String, List<String>> checkoutProvinceCityMap = new HashMap<>();
    private final Map<String, List<String>> checkoutCityBarangayMap = new HashMap<>();

    private interface OnMapLocationListener {
        void onLocationPicked(double latitude, double longitude);
    }

    public void showCheckoutDialog(SimpleCallback callback) {
        if (!isUserLoggedIn()) {
            Toast.makeText(this, "Please login before checkout", Toast.LENGTH_SHORT).show();
            loadFragment(new LoginFragment());
            return;
        }
        if (getCartItemCount() == 0) {
            Toast.makeText(this, "Your cart is empty", Toast.LENGTH_SHORT).show();
            return;
        }

        View dialogView = LayoutInflater.from(this).inflate(R.layout.dialog_checkout, null);
        TextView totalView = dialogView.findViewById(R.id.checkout_total_amount);
        TextView itemCountView = dialogView.findViewById(R.id.checkout_item_count);
        Spinner paymentSpinner = dialogView.findViewById(R.id.checkout_payment_method);
        LinearLayout gcashSection = dialogView.findViewById(R.id.checkout_gcash_section);
        EditText gcashReference = dialogView.findViewById(R.id.checkout_gcash_reference);
        TextView btnEnterAddress = dialogView.findViewById(R.id.btn_address_enter);
        TextView btnSavedAddress = dialogView.findViewById(R.id.btn_address_saved);
        ImageView gcashQrImage = dialogView.findViewById(R.id.checkout_gcash_qr);
        Button btnOpenGcash = dialogView.findViewById(R.id.btn_open_gcash);
        TextView savedPreview = dialogView.findViewById(R.id.checkout_saved_address_preview);
        LinearLayout manualFields = dialogView.findViewById(R.id.checkout_manual_fields);
        EditText streetInput = dialogView.findViewById(R.id.checkout_street);
        Spinner countrySpinner = dialogView.findViewById(R.id.checkout_country);
        Spinner provinceSpinner = dialogView.findViewById(R.id.checkout_province);
        Spinner citySpinner = dialogView.findViewById(R.id.checkout_city);
        Spinner barangaySpinner = dialogView.findViewById(R.id.checkout_barangay);
        EditText postalInput = dialogView.findViewById(R.id.checkout_postal);
        EditText descriptionInput = dialogView.findViewById(R.id.checkout_description);
        Button btnCurrentLocation = dialogView.findViewById(R.id.btn_use_current_location);
        TextView mapStatus = dialogView.findViewById(R.id.checkout_map_status);
        WebView mapWebView = dialogView.findViewById(R.id.checkout_map_webview);
        TextView checkoutError = dialogView.findViewById(R.id.checkout_error);
        Button placeOrderButton = dialogView.findViewById(R.id.btn_place_order);

        double cartTotal = getCartTotal();
        totalView.setText(String.format(Locale.US, "₱%.2f", cartTotal));
        itemCountView.setText(getCartItemCount() + " item(s) in cart");
        placeOrderButton.setText(String.format(Locale.US, "Place Order — ₱%.2f", cartTotal));

        bindCheckoutSpinner(paymentSpinner, Arrays.asList(
            "Select Payment Method",
            "GCash",
            "COD (Cash on Delivery)"
        ));
        bindCheckoutSpinner(countrySpinner, Arrays.asList("Philippines"));
        initCheckoutAddressMappings();
        setupCheckoutAddressSpinners(provinceSpinner, citySpinner, barangaySpinner);

        final boolean[] useSavedAddress = {false};
        final boolean[] mapPinned = {false};
        final double[] deliveryLat = {getRegisteredLatitude()};
        final double[] deliveryLng = {getRegisteredLongitude()};

        Runnable refreshGcashQr = () -> {
            Bitmap qr = createQrBitmap(buildGcashQrPayload(cartTotal), 420);
            if (qr != null) {
                gcashQrImage.setImageBitmap(qr);
            }
        };

        Runnable refreshAddressMode = () -> {
            if (useSavedAddress[0]) {
                manualFields.setVisibility(View.GONE);
                savedPreview.setVisibility(View.VISIBLE);
                String saved = getRegisteredShippingAddress();
                savedPreview.setText(saved.isEmpty()
                    ? "No saved address found. Complete registration or use Enter address."
                    : saved);
                deliveryLat[0] = getRegisteredLatitude();
                deliveryLng[0] = getRegisteredLongitude();
                mapPinned[0] = true;
                styleCheckoutAddressTab(btnEnterAddress, btnSavedAddress, false);
            } else {
                manualFields.setVisibility(View.VISIBLE);
                savedPreview.setVisibility(View.GONE);
                styleCheckoutAddressTab(btnEnterAddress, btnSavedAddress, true);
            }
        };
        btnEnterAddress.setOnClickListener(v -> {
            useSavedAddress[0] = false;
            refreshAddressMode.run();
        });
        btnSavedAddress.setOnClickListener(v -> {
            useSavedAddress[0] = true;
            refreshAddressMode.run();
        });
        refreshAddressMode.run();

        paymentSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                boolean isGcash = position == 1;
                gcashSection.setVisibility(isGcash ? View.VISIBLE : View.GONE);
                if (isGcash) {
                    refreshGcashQr.run();
                }
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });

        btnOpenGcash.setOnClickListener(v -> openGcashApp());

        setupDeliveryMapWebView(mapWebView, mapStatus, deliveryLat, deliveryLng, mapPinned, (lat, lng) -> {
            if (!useSavedAddress[0]) {
                mapStatus.setText("Pin set. Tap Use Current Location to autofill address, or edit fields manually.");
            }
        });

        btnCurrentLocation.setOnClickListener(v -> fetchCheckoutCurrentLocation(
            mapWebView,
            deliveryLat,
            deliveryLng,
            mapPinned,
            mapStatus,
            streetInput,
            provinceSpinner,
            citySpinner,
            barangaySpinner,
            postalInput
        ));

        AlertDialog dialog = new AlertDialog.Builder(this)
            .setTitle("Checkout")
            .setView(dialogView)
            .setNegativeButton("Close", null)
            .create();
        if (dialog.getWindow() != null) {
            dialog.getWindow().setLayout(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            );
        }

        placeOrderButton.setOnClickListener(v -> {
            checkoutError.setVisibility(View.GONE);
            int paymentIndex = paymentSpinner.getSelectedItemPosition();
            if (paymentIndex <= 0) {
                checkoutError.setText("Please select a payment method (GCash or COD).");
                checkoutError.setVisibility(View.VISIBLE);
                return;
            }
            String paymentMethod = paymentIndex == 1 ? "gcash" : "cash_on_delivery";

            Map<String, String> params = new HashMap<>();
            params.put("email", getRegisteredEmail());
            params.put("total_amount", String.format(Locale.US, "%.2f", cartTotal));
            params.put("payment_method", paymentMethod);
            params.put("contact_number", getRegisteredPhone());
            params.put("phone", getRegisteredPhone());

            if (paymentMethod.equals("gcash")) {
                String ref = gcashReference.getText().toString().trim();
                if (ref.length() < 6) {
                    checkoutError.setText("Enter a valid GCash reference number (at least 6 characters).");
                    checkoutError.setVisibility(View.VISIBLE);
                    return;
                }
                params.put("gcash_reference", ref);
            }

            String description = descriptionInput.getText().toString().trim();
            if (!description.isEmpty()) {
                params.put("delivery_description", description);
            }

            if (useSavedAddress[0]) {
                String savedAddress = getRegisteredShippingAddress();
                if (savedAddress.isEmpty()) {
                    checkoutError.setText("No registered address found. Use Enter address or update your profile.");
                    checkoutError.setVisibility(View.VISIBLE);
                    return;
                }
                params.put("delivery_address_mode", "saved_address");
                params.put("shipping_address", savedAddress);
                params.put("customer_latitude", String.format(Locale.US, "%.6f", deliveryLat[0]));
                params.put("customer_longitude", String.format(Locale.US, "%.6f", deliveryLng[0]));
                params.put("delivery_latitude", String.format(Locale.US, "%.6f", deliveryLat[0]));
                params.put("delivery_longitude", String.format(Locale.US, "%.6f", deliveryLng[0]));
            } else {
                String street = streetInput.getText().toString().trim();
                String country = countrySpinner.getSelectedItem() == null ? "" : countrySpinner.getSelectedItem().toString().trim();
                String province = provinceSpinner.getSelectedItem() == null ? "" : provinceSpinner.getSelectedItem().toString().trim();
                String city = citySpinner.getSelectedItem() == null ? "" : citySpinner.getSelectedItem().toString().trim();
                String barangay = barangaySpinner.getSelectedItem() == null ? "" : barangaySpinner.getSelectedItem().toString().trim();
                String postal = postalInput.getText().toString().trim();

                if (street.isEmpty() || province.isEmpty() || city.isEmpty() || barangay.isEmpty() || postal.isEmpty()) {
                    checkoutError.setText("Complete street, province, city, barangay, and postal code.");
                    checkoutError.setVisibility(View.VISIBLE);
                    return;
                }
                if (!mapPinned[0]) {
                    checkoutError.setText("Pin your delivery location on the map (tap map or use current location).");
                    checkoutError.setVisibility(View.VISIBLE);
                    return;
                }

                params.put("delivery_address_mode", "manual");
                params.put("delivery_address_line", street);
                params.put("street", street);
                params.put("delivery_country", country);
                params.put("country", country);
                params.put("delivery_province", province);
                params.put("delivery_city", city);
                params.put("delivery_barangay", barangay);
                params.put("delivery_postal_code", postal);
                params.put("delivery_latitude", String.format(Locale.US, "%.6f", deliveryLat[0]));
                params.put("delivery_longitude", String.format(Locale.US, "%.6f", deliveryLng[0]));
            }

            placeOrderButton.setEnabled(false);
            checkoutOnServer(params, new SimpleCallback() {
                @Override
                public void onSuccess(String message) {
                    placeOrderButton.setEnabled(true);
                    dialog.dismiss();
                    callback.onSuccess(message);
                }

                @Override
                public void onError(String message) {
                    placeOrderButton.setEnabled(true);
                    checkoutError.setText(message);
                    checkoutError.setVisibility(View.VISIBLE);
                }
            });
        });

        dialog.show();
    }

    private boolean hasCheckoutLocationPermission() {
        return ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
            == PackageManager.PERMISSION_GRANTED
            || ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION)
            == PackageManager.PERMISSION_GRANTED;
    }

    private void requestCheckoutLocationPermission() {
        ActivityCompat.requestPermissions(
            this,
            new String[]{
                Manifest.permission.ACCESS_FINE_LOCATION,
                Manifest.permission.ACCESS_COARSE_LOCATION
            },
            REQ_CHECKOUT_LOCATION
        );
    }

    private void styleCheckoutAddressTab(TextView enterTab, TextView savedTab, boolean enterSelected) {
        enterTab.setBackgroundResource(enterSelected
            ? R.drawable.bg_checkout_tab_selected
            : R.drawable.bg_checkout_tab_unselected);
        savedTab.setBackgroundResource(enterSelected
            ? R.drawable.bg_checkout_tab_unselected
            : R.drawable.bg_checkout_tab_selected);
        enterTab.setTextColor(enterSelected ? 0xFF0F766E : 0xFF6B7280);
        savedTab.setTextColor(enterSelected ? 0xFF6B7280 : 0xFF0F766E);
        enterTab.setTypeface(null, enterSelected ? Typeface.BOLD : Typeface.NORMAL);
        savedTab.setTypeface(null, enterSelected ? Typeface.NORMAL : Typeface.BOLD);
    }

    private String buildGcashQrPayload(double amount) {
        return "GCASH|MERCHANT:" + GCASH_MERCHANT_NAME
            + "|NUMBER:" + GCASH_MERCHANT_NUMBER
            + "|AMOUNT:" + String.format(Locale.US, "%.2f", amount)
            + "|REF:";
    }

    private Bitmap createQrBitmap(String content, int sizePx) {
        try {
            BitMatrix matrix = new MultiFormatWriter().encode(content, BarcodeFormat.QR_CODE, sizePx, sizePx);
            Bitmap bitmap = Bitmap.createBitmap(sizePx, sizePx, Bitmap.Config.ARGB_8888);
            for (int x = 0; x < sizePx; x++) {
                for (int y = 0; y < sizePx; y++) {
                    bitmap.setPixel(x, y, matrix.get(x, y) ? Color.BLACK : Color.WHITE);
                }
            }
            return bitmap;
        } catch (Exception e) {
            return null;
        }
    }

    private void openGcashApp() {
        String[] packages = {"com.mynt.gcash", "com.globe.gcash.android"};
        for (String pkg : packages) {
            Intent launch = getPackageManager().getLaunchIntentForPackage(pkg);
            if (launch != null) {
                launch.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                try {
                    startActivity(launch);
                    Toast.makeText(this, "Opening GCash app. Send payment to " + GCASH_MERCHANT_NUMBER, Toast.LENGTH_LONG).show();
                    return;
                } catch (Exception ignored) {
                    // try next package
                }
            }
        }
        try {
            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse("gcash://")));
        } catch (Exception e) {
            try {
                startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse("https://www.gcash.com/")));
            } catch (Exception ex) {
                Toast.makeText(this, "GCash app not found. Install GCash or pay using the QR code.", Toast.LENGTH_LONG).show();
            }
        }
    }

    private void applyCheckoutMapPin(WebView mapWebView, double lat, double lng) {
        if (mapWebView == null) {
            return;
        }
        String js = String.format(Locale.US, "setLocation(%.6f,%.6f,17)", lat, lng);
        mapWebView.evaluateJavascript(js, null);
    }

    @SuppressLint("MissingPermission")
    private void fetchCheckoutCurrentLocation(
        WebView mapWebView,
        double[] deliveryLat,
        double[] deliveryLng,
        boolean[] mapPinned,
        TextView mapStatus,
        EditText streetInput,
        Spinner provinceSpinner,
        Spinner citySpinner,
        Spinner barangaySpinner,
        EditText postalInput
    ) {
        if (!hasCheckoutLocationPermission()) {
            requestCheckoutLocationPermission();
            mapStatus.setText("Allow location access, then tap Use Current Location again.");
            return;
        }
        mapStatus.setText("Getting your current location...");
        LocationManager locationManager = (LocationManager) getSystemService(LOCATION_SERVICE);
        if (locationManager == null) {
            mapStatus.setText("Location service unavailable.");
            return;
        }

        Runnable applyLocation = () -> {
            deliveryLat[0] = lastCheckoutGpsLat;
            deliveryLng[0] = lastCheckoutGpsLng;
            mapPinned[0] = true;
            applyCheckoutMapPin(mapWebView, deliveryLat[0], deliveryLng[0]);
            reverseGeocodeCheckoutAddress(
                deliveryLat[0],
                deliveryLng[0],
                streetInput,
                provinceSpinner,
                citySpinner,
                barangaySpinner,
                postalInput,
                mapStatus
            );
        };

        Location cached = locationManager.getLastKnownLocation(LocationManager.GPS_PROVIDER);
        if (cached == null) {
            cached = locationManager.getLastKnownLocation(LocationManager.NETWORK_PROVIDER);
        }
        if (cached != null && System.currentTimeMillis() - cached.getTime() < 120000) {
            lastCheckoutGpsLat = cached.getLatitude();
            lastCheckoutGpsLng = cached.getLongitude();
            applyLocation.run();
            return;
        }

        final LocationListener[] listenerHolder = new LocationListener[1];
        listenerHolder[0] = new LocationListener() {
            @Override
            public void onLocationChanged(Location location) {
                locationManager.removeUpdates(this);
                lastCheckoutGpsLat = location.getLatitude();
                lastCheckoutGpsLng = location.getLongitude();
                mainHandler.post(applyLocation);
            }
        };

        mainHandler.postDelayed(() -> {
            if (listenerHolder[0] != null) {
                locationManager.removeUpdates(listenerHolder[0]);
            }
            if (!mapPinned[0]) {
                mapStatus.setText("Unable to get GPS. Tap the map to pin manually.");
            }
        }, 15000);

        try {
            locationManager.requestLocationUpdates(
                LocationManager.GPS_PROVIDER,
                0L,
                0f,
                listenerHolder[0],
                Looper.getMainLooper()
            );
        } catch (Exception e) {
            try {
                locationManager.requestLocationUpdates(
                    LocationManager.NETWORK_PROVIDER,
                    0L,
                    0f,
                    listenerHolder[0],
                    Looper.getMainLooper()
                );
            } catch (Exception ex) {
                mapStatus.setText("Unable to access GPS. Pin location on the map.");
            }
        }
    }

    private double lastCheckoutGpsLat = 6.1164;
    private double lastCheckoutGpsLng = 125.1716;

    private void reverseGeocodeCheckoutAddress(
        double lat,
        double lng,
        EditText streetInput,
        Spinner provinceSpinner,
        Spinner citySpinner,
        Spinner barangaySpinner,
        EditText postalInput,
        TextView mapStatus
    ) {
        mapStatus.setText("Looking up address for your location...");
        networkExecutor.execute(() -> {
            HttpURLConnection connection = null;
            try {
                String urlText = "https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat="
                    + URLEncoder.encode(String.format(Locale.US, "%.6f", lat), "UTF-8")
                    + "&lon=" + URLEncoder.encode(String.format(Locale.US, "%.6f", lng), "UTF-8");
                URL url = new URL(urlText);
                connection = (HttpURLConnection) url.openConnection();
                connection.setRequestMethod("GET");
                connection.setConnectTimeout(15000);
                connection.setReadTimeout(15000);
                connection.setRequestProperty("Accept", "application/json");
                connection.setRequestProperty("User-Agent", "VapeShopMobile/1.0 (checkout)");

                int code = connection.getResponseCode();
                InputStream stream = code >= 200 && code < 300
                    ? connection.getInputStream()
                    : connection.getErrorStream();
                String body = readStream(stream);
                JSONObject root = new JSONObject(body);
                JSONObject addr = root.optJSONObject("address");
                if (addr == null) {
                    throw new Exception("No address in response");
                }

                String house = addr.optString("house_number", "");
                String road = addr.optString("road", "");
                String street = (house + " " + road).trim();
                if (street.isEmpty()) {
                    street = addr.optString("pedestrian", "");
                }
                if (street.isEmpty()) {
                    street = addr.optString("residential", "");
                }

                String city = firstNonEmpty(
                    addr.optString("city", ""),
                    addr.optString("town", ""),
                    addr.optString("municipality", ""),
                    addr.optString("county", "")
                );
                String province = addr.optString("state", "");
                if (province.isEmpty()) {
                    province = addr.optString("region", "");
                }
                String postal = addr.optString("postcode", "");

                List<String> barangayCandidates = new ArrayList<>();
                for (String key : new String[]{"suburb", "neighbourhood", "village", "hamlet", "quarter", "city_district"}) {
                    String value = addr.optString(key, "").trim();
                    if (!value.isEmpty()) {
                        barangayCandidates.add(value);
                    }
                }

                final String streetFinal = street;
                final String cityFinal = city;
                final String provinceFinal = province;
                final String postalFinal = postal;
                final List<String> barangayCandidatesFinal = barangayCandidates;

                mainHandler.post(() -> {
                    if (!streetFinal.isEmpty()) {
                        streetInput.setText(streetFinal);
                    }
                    if (!provinceFinal.isEmpty()) {
                        setCheckoutSpinnerValue(provinceSpinner, provinceFinal);
                    }
                    if (!cityFinal.isEmpty()) {
                        setCheckoutSpinnerValue(citySpinner, cityFinal);
                        updateCheckoutBarangays(citySpinner, barangaySpinner, getSpinnerValue(citySpinner));
                    }
                    matchBarangaySpinner(barangaySpinner, barangayCandidatesFinal);
                    if (!postalFinal.isEmpty()) {
                        postalInput.setText(postalFinal);
                    }
                    mapStatus.setText("Location captured and address autofilled.");
                });
            } catch (Exception e) {
                mainHandler.post(() -> mapStatus.setText("Location captured. Address autofill unavailable — edit fields manually."));
            } finally {
                if (connection != null) {
                    connection.disconnect();
                }
            }
        });
    }

    private String firstNonEmpty(String... values) {
        for (String value : values) {
            if (value != null && !value.trim().isEmpty()) {
                return value.trim();
            }
        }
        return "";
    }

    private String getSpinnerValue(Spinner spinner) {
        if (spinner.getSelectedItem() == null) {
            return "";
        }
        return spinner.getSelectedItem().toString().trim();
    }

    private String normalizeLocationText(String value) {
        return value == null ? "" : value.toLowerCase(Locale.US)
            .replace(".", "")
            .replace("brgy", "")
            .replace("barangay", "")
            .replace("city", "")
            .replace("poblacion", "pob")
            .replaceAll("\\s+", " ")
            .trim();
    }

    private void setCheckoutSpinnerValue(Spinner spinner, String targetValue) {
        if (spinner == null || targetValue == null || targetValue.isEmpty()) {
            return;
        }
        String targetNorm = normalizeLocationText(targetValue);
        ArrayAdapter<?> adapter = (ArrayAdapter<?>) spinner.getAdapter();
        if (adapter == null) {
            return;
        }
        int partialIndex = -1;
        for (int i = 0; i < adapter.getCount(); i++) {
            String option = adapter.getItem(i).toString();
            String optNorm = normalizeLocationText(option);
            if (optNorm.equals(targetNorm)) {
                spinner.setSelection(i);
                return;
            }
            if (partialIndex < 0 && (optNorm.contains(targetNorm) || targetNorm.contains(optNorm))) {
                partialIndex = i;
            }
        }
        if (partialIndex >= 0) {
            spinner.setSelection(partialIndex);
            return;
        }
        List<String> items = new ArrayList<>();
        for (int i = 0; i < adapter.getCount(); i++) {
            items.add(adapter.getItem(i).toString());
        }
        items.add(targetValue);
        bindCheckoutSpinner(spinner, items);
        spinner.setSelection(items.size() - 1);
    }

    private void matchBarangaySpinner(Spinner barangaySpinner, List<String> candidates) {
        if (barangaySpinner == null || candidates == null || candidates.isEmpty()) {
            return;
        }
        ArrayAdapter<?> adapter = (ArrayAdapter<?>) barangaySpinner.getAdapter();
        if (adapter == null) {
            return;
        }
        List<String> options = new ArrayList<>();
        for (int i = 0; i < adapter.getCount(); i++) {
            options.add(adapter.getItem(i).toString());
        }
        for (String candidate : candidates) {
            String targetNorm = normalizeLocationText(candidate);
            if (targetNorm.isEmpty()) {
                continue;
            }
            for (int i = 0; i < options.size(); i++) {
                String optNorm = normalizeLocationText(options.get(i));
                if (optNorm.equals(targetNorm) || optNorm.contains(targetNorm) || targetNorm.contains(optNorm)) {
                    barangaySpinner.setSelection(i);
                    return;
                }
            }
        }
    }

    private void initCheckoutAddressMappings() {
        checkoutProvinceCityMap.clear();
        checkoutCityBarangayMap.clear();
        checkoutProvinceCityMap.put("South Cotabato", Arrays.asList("General Santos City"));
        checkoutCityBarangayMap.put("General Santos City", Arrays.asList(
            "Apopong", "Baluan", "Batomelong", "Buayan", "Bula", "Calumpang",
            "City Heights", "Conel", "Dadiangas East", "Dadiangas North",
            "Dadiangas South", "Dadiangas West", "Fatima", "Katangawan",
            "Labangal", "Lagao", "Ligaya", "Mabuhay", "Olympog", "San Isidro",
            "San Jose", "Siguel", "Sinawal", "Tambler", "Tinagacan", "Upper Labay"
        ));
    }

    private void setupCheckoutAddressSpinners(Spinner provinceSpinner, Spinner citySpinner, Spinner barangaySpinner) {
        bindCheckoutSpinner(provinceSpinner, Arrays.asList("South Cotabato"));
        provinceSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                String province = parent.getItemAtPosition(position).toString();
                List<String> cities = checkoutProvinceCityMap.get(province);
                if (cities == null || cities.isEmpty()) {
                    cities = Arrays.asList("General Santos City");
                }
                bindCheckoutSpinner(citySpinner, cities);
                citySpinner.setSelection(0);
                updateCheckoutBarangays(citySpinner, barangaySpinner, cities.get(0));
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });
        citySpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                updateCheckoutBarangays(citySpinner, barangaySpinner, parent.getItemAtPosition(position).toString());
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });
        if (provinceSpinner.getAdapter() != null && provinceSpinner.getAdapter().getCount() > 0) {
            provinceSpinner.setSelection(0);
        }
    }

    private void updateCheckoutBarangays(Spinner citySpinner, Spinner barangaySpinner, String city) {
        List<String> barangays = checkoutCityBarangayMap.get(city);
        if (barangays == null || barangays.isEmpty()) {
            barangays = Arrays.asList("Poblacion");
        }
        bindCheckoutSpinner(barangaySpinner, barangays);
        barangaySpinner.setSelection(0);
    }

    private void bindCheckoutSpinner(Spinner spinner, List<String> options) {
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
            this,
            R.layout.item_spinner_selected_small,
            options
        );
        adapter.setDropDownViewResource(R.layout.item_spinner_dropdown_small);
        spinner.setAdapter(adapter);
    }

    public void setupDeliveryMapWebView(
        WebView mapWebView,
        TextView statusView,
        double[] lat,
        double[] lng,
        boolean[] pinned
    ) {
        setupDeliveryMapWebView(mapWebView, statusView, lat, lng, pinned, null);
    }

    public void setupDeliveryMapWebView(
        WebView mapWebView,
        TextView statusView,
        double[] lat,
        double[] lng,
        boolean[] pinned,
        OnMapLocationListener listener
    ) {
        MapPinBridge mapBridge = new MapPinBridge(lat, lng, pinned, statusView, listener);
        WebSettings webSettings = mapWebView.getSettings();
        webSettings.setJavaScriptEnabled(true);
        webSettings.setDomStorageEnabled(true);
        webSettings.setGeolocationEnabled(true);
        mapWebView.addJavascriptInterface(mapBridge, "AndroidCheckoutMap");
        mapWebView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onGeolocationPermissionsShowPrompt(
                String origin,
                GeolocationPermissions.Callback callback
            ) {
                if (callback != null) {
                    callback.invoke(origin, true, false);
                }
            }
        });
        mapWebView.loadUrl("file:///android_asset/checkout_map.html");
    }

    private static class MapPinBridge {
        private final double[] lat;
        private final double[] lng;
        private final boolean[] pinned;
        private final TextView status;
        private final Handler handler;
        private final OnMapLocationListener listener;

        MapPinBridge(double[] lat, double[] lng, boolean[] pinned, TextView status, OnMapLocationListener listener) {
            this.lat = lat;
            this.lng = lng;
            this.pinned = pinned;
            this.status = status;
            this.handler = new Handler(Looper.getMainLooper());
            this.listener = listener;
        }

        @JavascriptInterface
        public void onLocationPicked(double latitude, double longitude) {
            handler.post(() -> {
                lat[0] = latitude;
                lng[0] = longitude;
                pinned[0] = true;
                if (status != null) {
                    status.setText("Location captured. Drag the pin to adjust.");
                }
                if (listener != null) {
                    listener.onLocationPicked(latitude, longitude);
                }
            });
        }
    }

    public void checkoutOnServer(Map<String, String> params, SimpleCallback callback) {
        if (!params.containsKey("email") || params.get("email") == null || params.get("email").isEmpty()) {
            params.put("email", getRegisteredEmail());
        }
        apiPost("checkout.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Checkout failed"));
                        return;
                    }
                    callback.onSuccess(root.optString("message", "Checkout successful"));
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void fetchProductsFromServer(ProductsCallback callback) {
        apiGet("products.php", new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Unable to fetch products"));
                        return;
                    }
                    JSONObject data = root.optJSONObject("data");
                    JSONArray productsArray = data != null ? data.optJSONArray("products") : null;
                    Map<String, ProductCatalogEntry> productsByName = new HashMap<>();
                    productCatalogById.clear();
                    productIdByName.clear();
                    if (productsArray != null) {
                        for (int i = 0; i < productsArray.length(); i++) {
                            ProductCatalogEntry entry = parseProductEntry(productsArray.optJSONObject(i));
                            if (entry == null) {
                                continue;
                            }
                            cacheProductCatalogEntry(entry);
                            productsByName.put(entry.name.toUpperCase(Locale.US), entry);
                        }
                    }
                    callback.onSuccess(productsByName);
                } catch (Exception e) {
                    callback.onError("Invalid product response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void loadCartFromServer(CartLoadCallback callback) {
        if (!isUserLoggedIn()) {
            callback.onSuccess();
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        apiPost("cart_list.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Unable to load cart"));
                        return;
                    }
                    JSONObject data = root.optJSONObject("data");
                    JSONArray items = data != null ? data.optJSONArray("items") : null;
                    cartItems.clear();
                    if (items != null) {
                        for (int i = 0; i < items.length(); i++) {
                            JSONObject item = items.optJSONObject(i);
                            if (item == null) {
                                continue;
                            }
                            String productName = item.optString("product_name", "").trim();
                            if (productName.isEmpty()) {
                                continue;
                            }
                            int productId = item.optInt("product_id", 0);
                            int variantIdRaw = item.optInt("variant_id", 0);
                            Integer variantId = variantIdRaw > 0 ? variantIdRaw : null;
                            String cartKey = productId > 0
                                ? buildCartKey(productId, variantId)
                                : productName + "::" + (variantId != null ? variantId : 0);
                            String spec = item.optString("spec", "");
                            double price = item.optDouble("unit_price", 0);
                            int quantity = item.optInt("quantity", 0);
                            int imageResId = getImageResForProductName(productName);
                            cartItems.put(cartKey, new CartItem(
                                cartKey,
                                productId,
                                variantId,
                                productName,
                                spec,
                                price,
                                imageResId,
                                Math.max(quantity, 1)
                            ));
                        }
                    }
                    callback.onSuccess();
                } catch (Exception e) {
                    callback.onError("Invalid cart response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void fetchOrdersFromServer(OrdersCallback callback) {
        if (!isUserLoggedIn()) {
            callback.onSuccess(new ArrayList<>());
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        apiPost("orders_list.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Unable to fetch orders"));
                        return;
                    }
                    JSONObject data = root.optJSONObject("data");
                    JSONArray rows = data != null ? data.optJSONArray("orders") : null;
                    List<OrderInfo> orders = new ArrayList<>();
                    if (rows != null) {
                        for (int i = 0; i < rows.length(); i++) {
                            JSONObject order = rows.optJSONObject(i);
                            if (order == null) {
                                continue;
                            }
                            JSONArray items = order.optJSONArray("items");
                            StringBuilder summary = new StringBuilder();
                            if (items != null) {
                                for (int j = 0; j < items.length(); j++) {
                                    JSONObject item = items.optJSONObject(j);
                                    if (item == null) {
                                        continue;
                                    }
                                    if (summary.length() > 0) {
                                        summary.append(", ");
                                    }
                                    summary.append(item.optString("product_name", "Item"))
                                        .append(" x")
                                        .append(item.optInt("quantity", 0));
                                }
                            }
                            JSONObject shipment = order.optJSONObject("shipment");
                            String shippingAddress = shipment != null ? shipment.optString("shipping_address", "") : "";
                            String shippingContact = shipment != null ? shipment.optString("contact_number", "") : "";
                            String deliveryStatus = order.optString("delivery_status", order.optString("status", "to_pay"));
                            OrderInfo info = new OrderInfo(
                                order.optInt("order_id", 0),
                                order.optString("reference_number", ""),
                                order.optString("order_date", ""),
                                deliveryStatus,
                                summary.length() == 0 ? "No items" : summary.toString(),
                                order.optDouble("total_amount", 0),
                                shippingAddress,
                                shippingContact
                            );
                            info.refundRequested = order.optBoolean("refund_requested", false);
                            info.canRequestReturn = order.optBoolean("can_request_return", false);
                            info.canPay = order.optBoolean("can_pay", false);
                            info.canCancel = order.optBoolean("can_cancel", false);
                            info.canConfirmReceived = order.optBoolean("can_confirm_received", false);
                            info.paymentStatus = order.optString("payment_status", "");
                            info.refundReason = order.optString("refund_reason", "");
                            String payoutMethod = order.optString("refund_method", "gcash");
                            info.refundMethod = payoutMethod.isEmpty() ? "gcash" : payoutMethod;
                            info.refundAccount = order.optString("refund_account", "");
                            info.returnCode = order.optString("return_token", "");
                            info.qrPayload = order.optString("qr_payload", "");
                            if (info.qrPayload.isEmpty() && !info.returnCode.isEmpty()) {
                                info.qrPayload = info.returnCode;
                            }
                            orders.add(info);
                        }
                    }
                    callback.onSuccess(orders);
                } catch (Exception e) {
                    callback.onError("Invalid order response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void fetchSupportMessagesFromServer(MessagesCallback callback) {
        fetchSupportMessagesFromServer(true, callback);
    }

    public void fetchSupportMessagesFromServer(boolean markRead, MessagesCallback callback) {
        if (!isUserLoggedIn()) {
            callback.onError("Please login to open messages");
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("mark_read", markRead ? "1" : "0");
        apiPost("support_messages_list.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Unable to load messages"));
                        return;
                    }
                    JSONObject data = root.optJSONObject("data");
                    if (data != null) {
                        supportUnreadCount = Math.max(0, data.optInt("unread_count", 0));
                        refreshMessageBadges();
                    }
                    List<SupportMessage> messages = parseSupportMessages(
                        data != null ? data.toString() : responseBody
                    );
                    callback.onSuccess(messages);
                } catch (Exception e) {
                    callback.onError("Invalid message response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void pollSupportUnreadCount() {
        if (!isUserLoggedIn()) {
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        apiPost("support_messages_unread.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        return;
                    }
                    JSONObject data = root.optJSONObject("data");
                    supportUnreadCount = data != null
                        ? Math.max(0, data.optInt("unread_count", 0))
                        : 0;
                    refreshMessageBadges();
                } catch (Exception ignored) {
                    // keep previous badge count
                }
            }

            @Override
            public void onError(String message) {
                // silent for background polling
            }
        });
    }

    public void startMessageBadgePolling() {
        if (!isUserLoggedIn() || !isCustomerRole()) {
            return;
        }
        if (messageBadgePollingActive) {
            return;
        }
        messageBadgePollingActive = true;
        mainHandler.removeCallbacks(messageBadgePollRunnable);
        mainHandler.post(messageBadgePollRunnable);
    }

    public void stopMessageBadgePolling() {
        messageBadgePollingActive = false;
        mainHandler.removeCallbacks(messageBadgePollRunnable);
    }

    public void refreshMessageBadges() {
        mainHandler.post(() -> {
            applyUnreadBadge(findViewById(R.id.nav_messages_badge));
            Fragment current = getSupportFragmentManager().findFragmentById(R.id.fragment_container);
            if (current != null && current.getView() != null) {
                applyUnreadBadge(current.getView().findViewById(R.id.messages_unread_badge));
            }
        });
    }

    private void applyUnreadBadge(TextView badge) {
        if (badge == null) {
            return;
        }
        if (supportUnreadCount > 0) {
            badge.setVisibility(View.VISIBLE);
            badge.setText(supportUnreadCount > 99 ? "99+" : String.valueOf(supportUnreadCount));
        } else {
            badge.setVisibility(View.GONE);
        }
    }

    public void sendSupportMessageToServer(
        String message,
        int relatedOrderId,
        String relatedOrderReference,
        String relatedProduct,
        SimpleCallback callback
    ) {
        if (!isUserLoggedIn()) {
            callback.onError("Please login to send message");
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("message", message);
        if (relatedOrderId > 0) {
            params.put("order_id", String.valueOf(relatedOrderId));
        }
        if (relatedOrderReference != null && !relatedOrderReference.trim().isEmpty()) {
            params.put("related_order", relatedOrderReference);
            params.put("order_reference", relatedOrderReference);
        }
        if (relatedProduct != null && !relatedProduct.trim().isEmpty()) {
            params.put("related_product", relatedProduct);
        }
        String lower = message.toLowerCase(Locale.US);
        if (lower.contains("human") || lower.contains("admin") || lower.contains("seller")) {
            params.put("support_target", "human");
        }
        apiPost("support_messages_send.php", params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Unable to send message"));
                        return;
                    }
                    callback.onSuccess(root.optString("message", "Message sent."));
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void submitReturnRefundToServer(
        int orderId,
        String orderReference,
        String reason,
        String payoutMethod,
        String payoutAccount,
        String payoutAccountName,
        List<Uri> attachments,
        SimpleCallback callback
    ) {
        if (!isUserLoggedIn()) {
            callback.onError("Please login to submit return/refund request");
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("request_type", "return_and_refund");
        params.put("reason", reason);
        params.put("payout_method", payoutMethod);
        params.put("payout_account", payoutAccount);
        params.put("payout_account_name", payoutAccountName);
        if (orderId > 0) {
            params.put("order_id", String.valueOf(orderId));
        }
        if (orderReference != null && !orderReference.isEmpty()) {
            params.put("order_reference", orderReference);
            params.put("reference_number", orderReference);
        }
        apiPostMultipart("return_refund_request.php", params, attachments, "return_evidence[]", new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Return/refund request failed"));
                        return;
                    }
                    callback.onSuccess(root.optString("message", "Return request submitted."));
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    public void performCustomerOrderAction(int orderId, String action, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("order_id", String.valueOf(orderId));
        params.put("action", action);
        apiPost("order_action.php", params, wrapJsonCallback(callback));
    }

    public void fetchOrderTracking(int orderId, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("order_id", String.valueOf(orderId));
        apiPost("order_tracking.php", params, callback);
    }

    private static final long LIVE_TRACKING_POLL_MS = 3000L;
    private int liveTrackingOrderId = 0;
    private WebView liveTrackingWebView;
    private TextView liveTrackingStatusView;
    private TextView liveTrackingMetaView;

    interface OrderDetailsUiUpdater {
        void onOrderStatusUpdated(String deliveryStatus, boolean canPay, boolean canCancel, boolean canConfirmReceived);
    }

    private OrderDetailsUiUpdater orderDetailsUiUpdater;

    public void setOrderDetailsUiUpdater(OrderDetailsUiUpdater updater) {
        orderDetailsUiUpdater = updater;
    }

    public void clearOrderDetailsUiUpdater() {
        orderDetailsUiUpdater = null;
    }

    public void refreshOrderDetailsFromServer(int orderId) {
        if (orderId <= 0 || !isUserLoggedIn()) {
            return;
        }
        fetchOrderTracking(orderId, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                applyLiveTrackingResponse(responseBody);
                notifyOrderDetailsUiFromTracking(responseBody);
            }

            @Override
            public void onError(String message) {
                // keep current UI
            }
        });
    }

    private void notifyOrderDetailsUiFromTracking(String responseBody) {
        if (orderDetailsUiUpdater == null) {
            return;
        }
        try {
            JSONObject root = new JSONObject(responseBody);
            if (!root.optBoolean("success", false)) {
                return;
            }
            JSONObject data = root.optJSONObject("data");
            JSONObject order = data != null ? data.optJSONObject("order") : null;
            if (order == null) {
                return;
            }
            String deliveryStatus = order.optString("delivery_status", order.optString("status", "to_pay"));
            boolean canPay = order.optBoolean("can_pay", false);
            boolean canCancel = order.optBoolean("can_cancel", false);
            boolean canConfirm = order.optBoolean("can_confirm_received", false)
                || "delivered".equalsIgnoreCase(deliveryStatus);
            mainHandler.post(() -> orderDetailsUiUpdater.onOrderStatusUpdated(
                deliveryStatus,
                canPay,
                canCancel,
                canConfirm
            ));
        } catch (Exception ignored) {
            // keep current UI
        }
    }
    private final Runnable liveTrackingPollRunnable = new Runnable() {
        @Override
        public void run() {
            pollLiveTrackingOnce();
        }
    };

    public static boolean shouldShowLiveTracking(String deliveryStatus) {
        if (deliveryStatus == null || deliveryStatus.isEmpty()) {
            return false;
        }
        String status = deliveryStatus.toLowerCase(Locale.US);
        return Arrays.asList(
            "to_ship",
            "ready_for_pickup",
            "accepted_by_rider",
            "delivered_to_rider",
            "to_receive",
            "delivered",
            "completed"
        ).contains(status);
    }

    public void setupLiveTrackingWebView(WebView webView) {
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        webView.loadUrl("file:///android_asset/live_tracking_map.html");
    }

    public void startLiveTracking(int orderId, WebView webView, TextView statusView, TextView metaView) {
        stopLiveTracking();
        if (orderId <= 0 || webView == null) {
            return;
        }
        liveTrackingOrderId = orderId;
        liveTrackingWebView = webView;
        liveTrackingStatusView = statusView;
        liveTrackingMetaView = metaView;
        if (statusView != null) {
            statusView.setText("Loading live map...");
        }
        if (metaView != null) {
            metaView.setText("Distance: — | ETA: —");
        }
        setupLiveTrackingWebView(webView);
        mainHandler.postDelayed(liveTrackingPollRunnable, 900);
    }

    public void stopLiveTracking() {
        liveTrackingOrderId = 0;
        liveTrackingWebView = null;
        liveTrackingStatusView = null;
        liveTrackingMetaView = null;
        mainHandler.removeCallbacks(liveTrackingPollRunnable);
    }

    public void refreshLiveTrackingNow() {
        if (liveTrackingOrderId > 0) {
            pollLiveTrackingOnce();
        }
    }

    private void pollLiveTrackingOnce() {
        final int orderId = liveTrackingOrderId;
        if (orderId <= 0) {
            return;
        }
        fetchOrderTracking(orderId, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                applyLiveTrackingResponse(responseBody);
                scheduleNextLiveTrackingPoll(orderId);
            }

            @Override
            public void onError(String message) {
                if (liveTrackingStatusView != null && liveTrackingOrderId == orderId) {
                    liveTrackingStatusView.setText(message);
                }
                scheduleNextLiveTrackingPoll(orderId);
            }
        });
    }

    private void scheduleNextLiveTrackingPoll(int orderId) {
        if (liveTrackingOrderId == orderId && orderId > 0) {
            mainHandler.postDelayed(liveTrackingPollRunnable, LIVE_TRACKING_POLL_MS);
        }
    }

    private boolean hasTrackingCoord(JSONObject tracking, String latKey, String lngKey) {
        if (tracking == null || tracking.isNull(latKey) || tracking.isNull(lngKey)) {
            return false;
        }
        double lat = tracking.optDouble(latKey, Double.NaN);
        double lng = tracking.optDouble(lngKey, Double.NaN);
        return !Double.isNaN(lat) && !Double.isNaN(lng) && !(lat == 0.0 && lng == 0.0);
    }

    private void applyLiveTrackingResponse(String responseBody) {
        if (liveTrackingWebView == null) {
            return;
        }
        try {
            JSONObject root = new JSONObject(responseBody);
            if (!root.optBoolean("success", false)) {
                if (liveTrackingStatusView != null) {
                    liveTrackingStatusView.setText(root.optString("message", "Tracking unavailable."));
                }
                return;
            }
            JSONObject data = root.optJSONObject("data");
            JSONObject tracking = data != null ? data.optJSONObject("tracking") : root.optJSONObject("tracking");
            if (tracking == null) {
                if (liveTrackingStatusView != null) {
                    liveTrackingStatusView.setText("Tracking unavailable.");
                }
                return;
            }

            final String trackingJson = tracking.toString();
            liveTrackingWebView.post(() ->
                liveTrackingWebView.evaluateJavascript("updateTracking(" + trackingJson + ")", null)
            );

            String status = tracking.optString("status", "");
            String phase = tracking.optString("phase", "");
            JSONObject rider = tracking.optJSONObject("rider");
            String riderName = rider != null ? rider.optString("name", "Assigned rider") : "Assigned rider";

            boolean hasRiderGps = hasTrackingCoord(tracking, "rider_latitude", "rider_longitude");
            boolean hasDeliveryGps = hasTrackingCoord(tracking, "delivery_latitude", "delivery_longitude");

            if (liveTrackingStatusView != null) {
                if ("to_receive".equalsIgnoreCase(status) && hasRiderGps) {
                    liveTrackingStatusView.setText("Out for Delivery | Rider: " + riderName);
                } else if ("pickup".equals(phase)) {
                    liveTrackingStatusView.setText("Rider on the way to store for pickup");
                } else if (hasDeliveryGps) {
                    String addr = tracking.optString("delivery_address", "");
                    liveTrackingStatusView.setText(addr.isEmpty()
                        ? "Your delivery location is pinned on the map."
                        : "Delivery Address: " + addr);
                } else {
                    liveTrackingStatusView.setText("Status: " + status.replace('_', ' '));
                }
            }

            if (liveTrackingMetaView != null) {
                if (hasRiderGps && hasDeliveryGps
                    && !tracking.isNull("distance_km")
                    && !tracking.isNull("eta_minutes")) {
                    liveTrackingMetaView.setText(String.format(
                        Locale.US,
                        "Distance: %.2f km | ETA: ~%d min",
                        tracking.optDouble("distance_km", 0),
                        tracking.optInt("eta_minutes", 0)
                    ));
                } else if ("to_receive".equalsIgnoreCase(status) && hasDeliveryGps) {
                    liveTrackingMetaView.setText("Waiting for live rider GPS location...");
                } else if (hasDeliveryGps) {
                    liveTrackingMetaView.setText("Store, your address, and rider appear on the map when available.");
                } else {
                    liveTrackingMetaView.setText("Distance: — | ETA: —");
                }
            }

            notifyOrderDetailsUiFromTracking(responseBody);
        } catch (Exception e) {
            if (liveTrackingStatusView != null) {
                liveTrackingStatusView.setText("Unable to load tracking.");
            }
        }
    }

    public void fetchAdminOrders(SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        apiPost("admin_orders_list.php", params, callback);
    }

    public void fetchRidersList(SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        apiPost("admin_riders_list.php", params, callback);
    }

    public void adminAssignRider(int orderId, int riderId, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("order_id", String.valueOf(orderId));
        params.put("rider_id", String.valueOf(riderId));
        apiPost("admin_assign_rider.php", params, wrapJsonCallback(callback));
    }

    public void adminUpdateDeliveryStatus(int orderId, String status, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("order_id", String.valueOf(orderId));
        params.put("status", status);
        apiPost("admin_update_delivery_status.php", params, wrapJsonCallback(callback));
    }

    public void fetchRiderOrders(String listType, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("list", listType);
        apiPost("rider_orders_list.php", params, callback);
    }

    public void riderUpdateStatus(int orderId, String status, Map<String, String> extra, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("order_id", String.valueOf(orderId));
        params.put("status", status);
        if (extra != null) {
            params.putAll(extra);
        }
        apiPost("rider_update_status.php", params, wrapJsonCallback(callback));
    }

    public void openDeliveryProofPicker(int orderId) {
        pendingProofOrderId = orderId;
        deliveryProofPickerLauncher.launch("image/*");
    }

    public void submitRiderProofToServer(int orderId, Uri proofUri, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("order_id", String.valueOf(orderId));
        params.put("final_rider_latitude", String.format(Locale.US, "%.6f", getRegisteredLatitude()));
        params.put("final_rider_longitude", String.format(Locale.US, "%.6f", getRegisteredLongitude()));
        List<Uri> files = new ArrayList<>();
        files.add(proofUri);
        apiPostMultipart("rider_submit_proof.php", params, files, "delivery_proof", new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Proof upload failed"));
                        return;
                    }
                    callback.onSuccess(root.optString("message", "Delivery proof submitted."));
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        });
    }

    private SimpleCallback wrapJsonCallback(SimpleCallback callback) {
        return new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    JSONObject root = new JSONObject(responseBody);
                    if (!root.optBoolean("success", false)) {
                        callback.onError(root.optString("message", "Request failed"));
                        return;
                    }
                    callback.onSuccess(root.optString("message", "Success"));
                } catch (Exception e) {
                    callback.onError("Invalid server response");
                }
            }

            @Override
            public void onError(String message) {
                callback.onError(message);
            }
        };
    }

    public void submitReviewToServer(
        String orderReference,
        String itemsSummary,
        int rating,
        String comment,
        SimpleCallback callback
    ) {
        if (!isUserLoggedIn()) {
            callback.onError("Please login to submit review");
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("order_reference", orderReference == null ? "" : orderReference);
        params.put("reference_number", orderReference == null ? "" : orderReference);
        params.put("product_name", itemsSummary == null ? "" : itemsSummary);
        params.put("rating", String.valueOf(rating));
        params.put("review_rating", String.valueOf(rating));
        params.put("comment", comment);
        params.put("review_text", comment);

        String[] endpoints = {
            "review_submit.php",
            "reviews_submit.php",
            "order_review_submit.php",
            "customer_review_submit.php"
        };
        trySubmitReviewEndpoint(endpoints, 0, params, callback);
    }

    private String buildAttachmentNames(List<Uri> attachments) {
        if (attachments == null || attachments.isEmpty()) {
            return "";
        }
        StringBuilder sb = new StringBuilder();
        for (Uri uri : attachments) {
            if (uri == null) {
                continue;
            }
            if (sb.length() > 0) {
                sb.append(" | ");
            }
            String last = uri.getLastPathSegment();
            sb.append(last == null ? "attachment" : last);
        }
        return sb.toString();
    }

    private void trySubmitReturnEndpoint(String[] endpoints, int index, Map<String, String> params, SimpleCallback callback) {
        if (index >= endpoints.length) {
            trySubmitReturnCustomerEndpoint(endpoints, 0, params, callback);
            return;
        }
        apiPost(endpoints[index], params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                if (isSuccessfulApiResponse(responseBody)) {
                    callback.onSuccess(extractApiMessage(responseBody, "Return request submitted."));
                } else {
                    trySubmitReturnEndpoint(endpoints, index + 1, params, callback);
                }
            }

            @Override
            public void onError(String message) {
                trySubmitReturnEndpoint(endpoints, index + 1, params, callback);
            }
        });
    }

    private void trySubmitReturnCustomerEndpoint(String[] endpoints, int index, Map<String, String> params, SimpleCallback callback) {
        if (index >= endpoints.length) {
            callback.onError("Return/refund endpoint not found. Please check backend API.");
            return;
        }
        apiPostCustomerModule(endpoints[index], params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                if (isSuccessfulApiResponse(responseBody)) {
                    callback.onSuccess(extractApiMessage(responseBody, "Return request submitted."));
                } else {
                    trySubmitReturnCustomerEndpoint(endpoints, index + 1, params, callback);
                }
            }

            @Override
            public void onError(String message) {
                trySubmitReturnCustomerEndpoint(endpoints, index + 1, params, callback);
            }
        });
    }

    private void trySubmitReviewEndpoint(String[] endpoints, int index, Map<String, String> params, SimpleCallback callback) {
        if (index >= endpoints.length) {
            trySubmitReviewCustomerEndpoint(endpoints, 0, params, callback);
            return;
        }
        apiPost(endpoints[index], params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                if (isSuccessfulApiResponse(responseBody)) {
                    callback.onSuccess(extractApiMessage(responseBody, "Review submitted."));
                } else {
                    trySubmitReviewEndpoint(endpoints, index + 1, params, callback);
                }
            }

            @Override
            public void onError(String message) {
                trySubmitReviewEndpoint(endpoints, index + 1, params, callback);
            }
        });
    }

    private void trySubmitReviewCustomerEndpoint(String[] endpoints, int index, Map<String, String> params, SimpleCallback callback) {
        if (index >= endpoints.length) {
            callback.onError("Review endpoint not found. Please check backend API.");
            return;
        }
        apiPostCustomerModule(endpoints[index], params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                if (isSuccessfulApiResponse(responseBody)) {
                    callback.onSuccess(extractApiMessage(responseBody, "Review submitted."));
                } else {
                    trySubmitReviewCustomerEndpoint(endpoints, index + 1, params, callback);
                }
            }

            @Override
            public void onError(String message) {
                trySubmitReviewCustomerEndpoint(endpoints, index + 1, params, callback);
            }
        });
    }

    private boolean isSuccessfulApiResponse(String responseBody) {
        if (responseBody == null) {
            return false;
        }
        String raw = responseBody.trim();
        if (raw.isEmpty() || raw.startsWith("<")) {
            return false;
        }
        try {
            JSONObject root = new JSONObject(raw);
            return root.optBoolean("success", false)
                || "success".equalsIgnoreCase(root.optString("status", ""))
                || root.optInt("code", 0) == 200;
        } catch (Exception ignored) {
            return raw.toLowerCase(Locale.US).contains("success");
        }
    }

    private String extractApiMessage(String responseBody, String fallback) {
        if (responseBody == null || responseBody.trim().isEmpty()) {
            return fallback;
        }
        try {
            JSONObject root = new JSONObject(responseBody);
            String message = root.optString("message", "");
            return message == null || message.trim().isEmpty() ? fallback : message;
        } catch (Exception e) {
            return fallback;
        }
    }

    private void tryFetchMessagesEndpoint(String[] endpoints, int index, Map<String, String> params, MessagesCallback callback) {
        if (index >= endpoints.length) {
            String[] customerEndpoints = {
                "messages_list.php",
                "support_chat_list.php",
                "chat_list.php"
            };
            tryFetchCustomerMessagesEndpoint(customerEndpoints, 0, params, callback);
            return;
        }
        apiPost(endpoints[index], params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    if (responseBody == null || responseBody.trim().isEmpty() || responseBody.trim().startsWith("<")) {
                        tryFetchMessagesEndpoint(endpoints, index + 1, params, callback);
                        return;
                    }
                    List<SupportMessage> messages = parseSupportMessages(responseBody);
                    callback.onSuccess(messages);
                } catch (Exception e) {
                    tryFetchMessagesEndpoint(endpoints, index + 1, params, callback);
                }
            }

            @Override
            public void onError(String message) {
                tryFetchMessagesEndpoint(endpoints, index + 1, params, callback);
            }
        });
    }

    private void tryFetchCustomerMessagesEndpoint(String[] endpoints, int index, Map<String, String> params, MessagesCallback callback) {
        if (index >= endpoints.length) {
            callback.onError("Chat API unavailable. Please check server chat endpoints.");
            return;
        }
        apiPostCustomerModule(endpoints[index], params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                try {
                    if (responseBody == null || responseBody.trim().isEmpty() || responseBody.trim().startsWith("<")) {
                        tryFetchCustomerMessagesEndpoint(endpoints, index + 1, params, callback);
                        return;
                    }
                    List<SupportMessage> messages = parseSupportMessages(responseBody);
                    callback.onSuccess(messages);
                } catch (Exception e) {
                    tryFetchCustomerMessagesEndpoint(endpoints, index + 1, params, callback);
                }
            }

            @Override
            public void onError(String message) {
                tryFetchCustomerMessagesEndpoint(endpoints, index + 1, params, callback);
            }
        });
    }

    private void trySendMessageEndpoint(String[] endpoints, int index, Map<String, String> params, SimpleCallback callback) {
        if (index >= endpoints.length) {
            String[] customerEndpoints = {
                "messages_send.php",
                "support_chat_send.php",
                "chat_send.php"
            };
            trySendCustomerMessageEndpoint(customerEndpoints, 0, params, callback);
            return;
        }
        apiPost(endpoints[index], params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                if (responseBody == null || responseBody.trim().isEmpty() || responseBody.trim().startsWith("<")) {
                    trySendMessageEndpoint(endpoints, index + 1, params, callback);
                    return;
                }
                try {
                    JSONObject root = new JSONObject(responseBody);
                    boolean success = root.optBoolean("success", false)
                        || "success".equalsIgnoreCase(root.optString("status", ""))
                        || root.optInt("code", 0) == 200;
                    if (!success) {
                        trySendMessageEndpoint(endpoints, index + 1, params, callback);
                        return;
                    }
                    callback.onSuccess(root.optString("message", "Reply sent."));
                } catch (Exception e) {
                    if (responseBody.toLowerCase(Locale.US).contains("success")) {
                        callback.onSuccess("Reply sent.");
                    } else {
                        trySendMessageEndpoint(endpoints, index + 1, params, callback);
                    }
                }
            }

            @Override
            public void onError(String message) {
                trySendMessageEndpoint(endpoints, index + 1, params, callback);
            }
        });
    }

    private void trySendCustomerMessageEndpoint(String[] endpoints, int index, Map<String, String> params, SimpleCallback callback) {
        if (index >= endpoints.length) {
            callback.onError("Unable to send message. Chat endpoint not found.");
            return;
        }
        apiPostCustomerModule(endpoints[index], params, new SimpleCallback() {
            @Override
            public void onSuccess(String responseBody) {
                if (responseBody == null || responseBody.trim().isEmpty() || responseBody.trim().startsWith("<")) {
                    trySendCustomerMessageEndpoint(endpoints, index + 1, params, callback);
                    return;
                }
                try {
                    JSONObject root = new JSONObject(responseBody);
                    boolean success = root.optBoolean("success", false)
                        || "success".equalsIgnoreCase(root.optString("status", ""))
                        || root.optInt("code", 0) == 200;
                    if (!success) {
                        trySendCustomerMessageEndpoint(endpoints, index + 1, params, callback);
                        return;
                    }
                    callback.onSuccess(root.optString("message", "Reply sent."));
                } catch (Exception e) {
                    if (responseBody.toLowerCase(Locale.US).contains("success")) {
                        callback.onSuccess("Reply sent.");
                    } else {
                        trySendCustomerMessageEndpoint(endpoints, index + 1, params, callback);
                    }
                }
            }

            @Override
            public void onError(String message) {
                trySendCustomerMessageEndpoint(endpoints, index + 1, params, callback);
            }
        });
    }

    private List<SupportMessage> parseSupportMessages(String responseBody) throws Exception {
        List<SupportMessage> messages = new ArrayList<>();
        String raw = responseBody == null ? "" : responseBody.trim();

        JSONArray rows = null;
        if (raw.startsWith("[")) {
            rows = new JSONArray(raw);
        } else {
            JSONObject root = new JSONObject(raw);
            JSONArray direct = root.optJSONArray("messages");
            JSONObject data = root.optJSONObject("data");
            JSONObject convo = root.optJSONObject("conversation");
            rows = direct;
            if (rows == null && data != null) {
                rows = data.optJSONArray("messages");
            }
            if (rows == null && convo != null) {
                rows = convo.optJSONArray("messages");
            }
            if (rows == null && data != null) {
                JSONObject thread = data.optJSONObject("conversation");
                if (thread != null) {
                    rows = thread.optJSONArray("messages");
                }
            }
        }

        if (rows == null) {
            return messages;
        }

        String customerEmail = getRegisteredEmail().toLowerCase(Locale.US);
        for (int i = 0; i < rows.length(); i++) {
            JSONObject item = rows.optJSONObject(i);
            if (item == null) {
                continue;
            }
            String sender = item.optString("sender_name",
                item.optString("sender_role", item.optString("sender", "Support")));
            String body = item.optString("message",
                item.optString("content", item.optString("body", item.optString("text", ""))));
            String createdAt = item.optString("created_label",
                item.optString("created_at",
                    item.optString("sent_at", item.optString("timestamp", item.optString("date", "")))));
            int messageId = item.optInt("id", 0);
            String role = item.optString("sender_role",
                item.optString("role", item.optString("type", ""))).toLowerCase(Locale.US);
            String messageType = item.optString("message_type", "text").toLowerCase(Locale.US);
            String senderEmail = item.optString("sender_email", item.optString("email", "")).toLowerCase(Locale.US);
            boolean fromCustomer = "customer".equals(role)
                || (role.isEmpty() && senderEmail.equals(customerEmail));
            if ("system".equals(messageType)) {
                role = "system";
                sender = "System";
                fromCustomer = false;
            } else if ("chatbot".equals(role) || "bot".equals(role)) {
                role = "chatbot";
                sender = "Chatbot";
                fromCustomer = false;
            } else if ("admin".equals(role) || "staff".equals(role)) {
                role = "admin";
                sender = "Admin";
                fromCustomer = false;
            } else if ("rider".equals(role)) {
                role = "rider";
                sender = "Rider";
                fromCustomer = false;
            } else if (fromCustomer) {
                role = "customer";
                sender = "You";
            } else if (sender.isEmpty()) {
                sender = "Admin";
                role = "admin";
            }
            if (body != null && !body.trim().isEmpty()) {
                messages.add(new SupportMessage(
                    messageId,
                    sender,
                    body,
                    createdAt,
                    role,
                    messageType,
                    fromCustomer
                ));
            }
        }
        Collections.sort(messages, new Comparator<SupportMessage>() {
            @Override
            public int compare(SupportMessage left, SupportMessage right) {
                if (left.id > 0 && right.id > 0 && left.id != right.id) {
                    return Integer.compare(left.id, right.id);
                }
                String leftTime = left.createdAt == null ? "" : left.createdAt;
                String rightTime = right.createdAt == null ? "" : right.createdAt;
                int byTime = leftTime.compareTo(rightTime);
                if (byTime != 0) {
                    return byTime;
                }
                return Integer.compare(left.id, right.id);
            }
        });
        return messages;
    }

    private int getImageResForProductName(String productName) {
        String key = productName.toUpperCase(Locale.US).trim();
        switch (key) {
            case "BLACK ELITE V2": return R.drawable.black_elite_v2;
            case "BLACK ELITE V1": return R.drawable.black_elite_v1;
            case "BLACK? V2": return R.drawable.black_question_v2;
            case "CRYSM ELITE": return R.drawable.crysm_elite;
            case "VAPOR ZERO": return R.drawable.vapor_zero;
            case "UOTOFO": return R.drawable.uotofo;
            case "XVAPE SLIMBAR": return R.drawable.xvape_slimbar;
            case "KALO V2": return R.drawable.kalo_v2;
            case "MINICAN": return R.drawable.minican;
            case "BLACK V1 BATTERY": return R.drawable.black_v1_battery;
            case "X-VAPE SLIMBAR DEVICE": return R.drawable.xvape_slimbar_device;
            case "POD FORMULA": return R.drawable.pod_formula;
            case "STORM": return R.drawable.storm;
            case "BL?CK": return R.drawable.bl_ck;
            case "VI BAR": return R.drawable.vi_bar;
            default: return R.drawable.black_elite_v2;
        }
    }

    private void apiPost(String endpoint, Map<String, String> params, SimpleCallback callback) {
        networkExecutor.execute(() -> {
            String lastError = "";
            String lastUrlTried = "";
            for (String baseUrl : API_BASE_URLS) {
                HttpURLConnection connection = null;
                try {
                    lastUrlTried = baseUrl + endpoint;
                    URL url = new URL(lastUrlTried);
                    connection = (HttpURLConnection) url.openConnection();
                    connection.setRequestMethod("POST");
                    connection.setConnectTimeout(15000);
                    connection.setReadTimeout(15000);
                    connection.setDoOutput(true);
                    connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
                    connection.setRequestProperty("Accept", "application/json");

                    String body = buildFormBody(params);
                    try (OutputStream os = connection.getOutputStream()) {
                        os.write(body.getBytes(StandardCharsets.UTF_8));
                    }

                    int code = connection.getResponseCode();
                    InputStream stream = code >= 200 && code < 300
                        ? connection.getInputStream()
                        : connection.getErrorStream();
                    String response = readStream(stream);
                    if (response == null || response.isEmpty()) {
                        response = "{\"success\":false,\"message\":\"Empty server response\"}";
                    }
                    final String responseFinal = response;
                    mainHandler.post(() -> callback.onSuccess(responseFinal));
                    return;
                } catch (Exception e) {
                    lastError = e.getMessage() == null ? "Unknown network error" : e.getMessage();
                } finally {
                    if (connection != null) {
                        connection.disconnect();
                    }
                }
            }
            final String finalLastError = lastError;
            final String finalLastUrlTried = lastUrlTried;
            mainHandler.post(() -> callback.onError("Cannot connect to server. URL: " + finalLastUrlTried + " | " + finalLastError));
        });
    }

    private void apiPostMultipart(
        String endpoint,
        Map<String, String> params,
        List<Uri> fileUris,
        String fileFieldName,
        SimpleCallback callback
    ) {
        networkExecutor.execute(() -> {
            String lastError = "";
            String lastUrlTried = "";
            for (String baseUrl : API_BASE_URLS) {
                HttpURLConnection connection = null;
                try {
                    lastUrlTried = baseUrl + endpoint;
                    String boundary = "----VapeShopBoundary" + System.currentTimeMillis();
                    URL url = new URL(lastUrlTried);
                    connection = (HttpURLConnection) url.openConnection();
                    connection.setRequestMethod("POST");
                    connection.setConnectTimeout(60000);
                    connection.setReadTimeout(60000);
                    connection.setDoOutput(true);
                    connection.setRequestProperty("Content-Type", "multipart/form-data; boundary=" + boundary);
                    connection.setRequestProperty("Accept", "application/json");

                    ByteArrayOutputStream bodyStream = new ByteArrayOutputStream();
                    if (params != null) {
                        for (Map.Entry<String, String> entry : params.entrySet()) {
                            writeMultipartField(bodyStream, boundary, entry.getKey(), entry.getValue());
                        }
                    }
                    if (fileUris != null) {
                        int fileIndex = 0;
                        for (Uri uri : fileUris) {
                            if (uri == null) {
                                continue;
                            }
                            byte[] fileBytes = readUriBytes(uri);
                            if (fileBytes == null || fileBytes.length == 0) {
                                continue;
                            }
                            String fileName = resolveUriFileName(uri, fileIndex);
                            String mimeType = guessMimeType(fileName);
                            writeMultipartFile(
                                bodyStream,
                                boundary,
                                fileFieldName == null ? "return_evidence[]" : fileFieldName,
                                fileName,
                                mimeType,
                                fileBytes
                            );
                            fileIndex++;
                        }
                    }
                    bodyStream.write(("--" + boundary + "--\r\n").getBytes(StandardCharsets.UTF_8));

                    try (OutputStream os = connection.getOutputStream()) {
                        os.write(bodyStream.toByteArray());
                    }

                    int code = connection.getResponseCode();
                    InputStream stream = code >= 200 && code < 300
                        ? connection.getInputStream()
                        : connection.getErrorStream();
                    String response = readStream(stream);
                    if (response == null || response.isEmpty()) {
                        response = "{\"success\":false,\"message\":\"Empty server response\"}";
                    }
                    final String responseFinal = response;
                    mainHandler.post(() -> callback.onSuccess(responseFinal));
                    return;
                } catch (Exception e) {
                    lastError = e.getMessage() == null ? "Unknown network error" : e.getMessage();
                } finally {
                    if (connection != null) {
                        connection.disconnect();
                    }
                }
            }
            final String finalLastError = lastError;
            final String finalLastUrlTried = lastUrlTried;
            mainHandler.post(() -> callback.onError("Cannot connect to server. URL: " + finalLastUrlTried + " | " + finalLastError));
        });
    }

    private void writeMultipartField(ByteArrayOutputStream stream, String boundary, String name, String value) throws Exception {
        stream.write(("--" + boundary + "\r\n").getBytes(StandardCharsets.UTF_8));
        stream.write(("Content-Disposition: form-data; name=\"" + name + "\"\r\n\r\n").getBytes(StandardCharsets.UTF_8));
        stream.write((value == null ? "" : value).getBytes(StandardCharsets.UTF_8));
        stream.write("\r\n".getBytes(StandardCharsets.UTF_8));
    }

    private void writeMultipartFile(
        ByteArrayOutputStream stream,
        String boundary,
        String fieldName,
        String fileName,
        String mimeType,
        byte[] fileBytes
    ) throws Exception {
        stream.write(("--" + boundary + "\r\n").getBytes(StandardCharsets.UTF_8));
        stream.write(
            ("Content-Disposition: form-data; name=\"" + fieldName + "\"; filename=\"" + fileName + "\"\r\n")
                .getBytes(StandardCharsets.UTF_8)
        );
        stream.write(("Content-Type: " + mimeType + "\r\n\r\n").getBytes(StandardCharsets.UTF_8));
        stream.write(fileBytes);
        stream.write("\r\n".getBytes(StandardCharsets.UTF_8));
    }

    private byte[] readUriBytes(Uri uri) {
        try (InputStream inputStream = getContentResolver().openInputStream(uri)) {
            if (inputStream == null) {
                return null;
            }
            ByteArrayOutputStream buffer = new ByteArrayOutputStream();
            byte[] data = new byte[8192];
            int read;
            while ((read = inputStream.read(data)) != -1) {
                buffer.write(data, 0, read);
            }
            return buffer.toByteArray();
        } catch (Exception e) {
            return null;
        }
    }

    private String resolveUriFileName(Uri uri, int index) {
        String name = null;
        try (Cursor cursor = getContentResolver().query(uri, null, null, null, null)) {
            if (cursor != null && cursor.moveToFirst()) {
                int nameIndex = cursor.getColumnIndex(OpenableColumns.DISPLAY_NAME);
                if (nameIndex >= 0) {
                    name = cursor.getString(nameIndex);
                }
            }
        } catch (Exception ignored) {
        }
        if (name == null || name.trim().isEmpty()) {
            String segment = uri.getLastPathSegment();
            name = segment == null ? ("evidence_" + index + ".jpg") : segment;
        }
        return name;
    }

    private String guessMimeType(String fileName) {
        String lower = fileName.toLowerCase(Locale.US);
        if (lower.endsWith(".png")) {
            return "image/png";
        }
        if (lower.endsWith(".gif")) {
            return "image/gif";
        }
        if (lower.endsWith(".webp")) {
            return "image/webp";
        }
        if (lower.endsWith(".mp4")) {
            return "video/mp4";
        }
        if (lower.endsWith(".webm")) {
            return "video/webm";
        }
        if (lower.endsWith(".mov")) {
            return "video/quicktime";
        }
        return "image/jpeg";
    }

    private void apiPostCustomerModule(String endpoint, Map<String, String> params, SimpleCallback callback) {
        networkExecutor.execute(() -> {
            String lastError = "";
            String lastUrlTried = "";
            for (String baseUrl : API_BASE_URLS) {
                String customerBase = baseUrl.replace("/mobile_api/", "/customer/");
                HttpURLConnection connection = null;
                try {
                    lastUrlTried = customerBase + endpoint;
                    URL url = new URL(lastUrlTried);
                    connection = (HttpURLConnection) url.openConnection();
                    connection.setRequestMethod("POST");
                    connection.setConnectTimeout(15000);
                    connection.setReadTimeout(15000);
                    connection.setDoOutput(true);
                    connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
                    connection.setRequestProperty("Accept", "application/json");

                    String body = buildFormBody(params);
                    try (OutputStream os = connection.getOutputStream()) {
                        os.write(body.getBytes(StandardCharsets.UTF_8));
                    }

                    int code = connection.getResponseCode();
                    InputStream stream = code >= 200 && code < 300
                        ? connection.getInputStream()
                        : connection.getErrorStream();
                    String response = readStream(stream);
                    if (response == null || response.isEmpty()) {
                        response = "{\"success\":false,\"message\":\"Empty server response\"}";
                    }
                    final String responseFinal = response;
                    mainHandler.post(() -> callback.onSuccess(responseFinal));
                    return;
                } catch (Exception e) {
                    lastError = e.getMessage() == null ? "Unknown network error" : e.getMessage();
                } finally {
                    if (connection != null) {
                        connection.disconnect();
                    }
                }
            }
            final String finalLastError = lastError;
            final String finalLastUrlTried = lastUrlTried;
            mainHandler.post(() -> callback.onError("Cannot connect to server. URL: " + finalLastUrlTried + " | " + finalLastError));
        });
    }

    private void apiGet(String endpoint, SimpleCallback callback) {
        networkExecutor.execute(() -> {
            String lastError = "";
            String lastUrlTried = "";
            for (String baseUrl : API_BASE_URLS) {
                HttpURLConnection connection = null;
                try {
                    lastUrlTried = baseUrl + endpoint;
                    URL url = new URL(lastUrlTried);
                    connection = (HttpURLConnection) url.openConnection();
                    connection.setRequestMethod("GET");
                    connection.setConnectTimeout(15000);
                    connection.setReadTimeout(15000);
                    connection.setRequestProperty("Accept", "application/json");

                    int code = connection.getResponseCode();
                    InputStream stream = code >= 200 && code < 300
                        ? connection.getInputStream()
                        : connection.getErrorStream();
                    String response = readStream(stream);
                    if (response == null || response.isEmpty()) {
                        response = "{\"success\":false,\"message\":\"Empty server response\"}";
                    }
                    final String responseFinal = response;
                    mainHandler.post(() -> callback.onSuccess(responseFinal));
                    return;
                } catch (Exception e) {
                    lastError = e.getMessage() == null ? "Unknown network error" : e.getMessage();
                } finally {
                    if (connection != null) {
                        connection.disconnect();
                    }
                }
            }
            final String finalLastError = lastError;
            final String finalLastUrlTried = lastUrlTried;
            mainHandler.post(() -> callback.onError("Cannot connect to server. URL: " + finalLastUrlTried + " | " + finalLastError));
        });
    }

    private String buildFormBody(Map<String, String> params) throws Exception {
        StringBuilder body = new StringBuilder();
        for (Map.Entry<String, String> entry : params.entrySet()) {
            if (body.length() > 0) {
                body.append("&");
            }
            body.append(URLEncoder.encode(entry.getKey(), "UTF-8"));
            body.append("=");
            body.append(URLEncoder.encode(entry.getValue() == null ? "" : entry.getValue(), "UTF-8"));
        }
        return body.toString();
    }

    private String readStream(InputStream stream) throws Exception {
        if (stream == null) {
            return "";
        }
        StringBuilder sb = new StringBuilder();
        try (BufferedReader reader = new BufferedReader(new InputStreamReader(stream, StandardCharsets.UTF_8))) {
            String line;
            while ((line = reader.readLine()) != null) {
                sb.append(line);
            }
        }
        return sb.toString();
    }

    private double parsePrice(String rawPrice) {
        String normalized = rawPrice
            .replace("PHP", "")
            .replace("₱", "")
            .replace(",", "")
            .trim();
        try {
            return Double.parseDouble(normalized);
        } catch (NumberFormatException e) {
            return 0.0;
        }
    }

    private double parseDoubleSafe(String value, double fallback) {
        if (value == null || value.trim().isEmpty()) {
            return fallback;
        }
        try {
            return Double.parseDouble(value.trim());
        } catch (NumberFormatException e) {
            return fallback;
        }
    }

    public void updateCartQuantity(String cartKey, int delta) {
        CartItem item = cartItems.get(cartKey);
        if (item == null) {
            return;
        }
        item.quantity += delta;
        if (item.quantity <= 0) {
            cartItems.remove(cartKey);
        }
    }

    public List<CartItem> getCartItems() {
        return new ArrayList<>(cartItems.values());
    }

    public int getCartItemCount() {
        int count = 0;
        for (CartItem item : cartItems.values()) {
            count += item.quantity;
        }
        return count;
    }

    public double getCartTotal() {
        double total = 0.0;
        for (CartItem item : cartItems.values()) {
            total += item.price * item.quantity;
        }
        return total;
    }

    public void clearCart() {
        cartItems.clear();
    }

    private boolean hasRegisteredAccount() {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        return !prefs.getString(KEY_USER_EMAIL, "").isEmpty();
    }

    private boolean registerAccount(String fullName, String email, String password) {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        String savedEmail = prefs.getString(KEY_USER_EMAIL, "");
        if (!savedEmail.isEmpty() && savedEmail.equalsIgnoreCase(email)) {
            return false;
        }
        prefs.edit()
            .putString(KEY_USER_FULL_NAME, fullName)
            .putString(KEY_USER_EMAIL, email)
            .putString(KEY_USER_PASSWORD, password)
            .apply();
        return true;
    }

    private boolean validateLogin(String email, String password) {
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        String savedEmail = prefs.getString(KEY_USER_EMAIL, "");
        String savedPassword = prefs.getString(KEY_USER_PASSWORD, "");
        return email.equalsIgnoreCase(savedEmail) && password.equals(savedPassword);
    }

    public void onLogout() {
        stopLiveTracking();
        stopMessageBadgePolling();
        supportUnreadCount = 0;
        refreshMessageBadges();
        isLoggedIn = false;
        currentUserRole = "customer";
        currentUserId = 0;
        setRoleNavigationVisible(true);
        loadFragment(new LoginFragment());
        Toast.makeText(this, "Logged out", Toast.LENGTH_SHORT).show();
    }

    public static class LoginFragment extends Fragment {
        @Override
        public View onCreateView(LayoutInflater inflater, android.view.ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_login, container, false);
            EditText email = view.findViewById(R.id.login_email);
            EditText password = view.findViewById(R.id.login_password);
            TextView inlineError = view.findViewById(R.id.login_inline_error);
            Button loginButton = view.findViewById(R.id.btn_login);
            Button registerButton = view.findViewById(R.id.btn_go_register);
            loginButton.setOnClickListener(v -> {
                inlineError.setVisibility(View.GONE);
                String emailValue = email.getText().toString().trim();
                String passwordValue = password.getText().toString().trim();
                if (emailValue.isEmpty() || passwordValue.isEmpty()) {
                    inlineError.setText("Enter email and password.");
                    inlineError.setVisibility(View.VISIBLE);
                    return;
                }
                MainActivity activity = (MainActivity) requireActivity();
                loginButton.setEnabled(false);
                loginButton.setText("Logging in...");
                activity.loginWithServer(emailValue, passwordValue, new AuthCallback() {
                    @Override
                    public void onSuccess(String fullName, String email) {
                        inlineError.setVisibility(View.GONE);
                        activity.onLoginSuccess();
                    }

                    @Override
                    public void onError(String message) {
                        loginButton.setEnabled(true);
                        loginButton.setText("Login");
                        inlineError.setText(message);
                        inlineError.setVisibility(View.VISIBLE);
                    }
                });
            });
            registerButton.setOnClickListener(v ->
                ((MainActivity) requireActivity()).loadFragment(new RegisterFragment()));
            return view;
        }
    }

    public static class RegisterFragment extends Fragment {
        private Uri selectedIdUri;
        private TextView selectedIdText;
        private Spinner provinceSpinner;
        private Spinner citySpinner;
        private Spinner barangaySpinner;
        private final Map<String, List<String>> provinceCityMap = new HashMap<>();
        private final Map<String, List<String>> cityBarangayMap = new HashMap<>();
        private final double[] deliveryLat = {6.1164};
        private final double[] deliveryLng = {125.1716};
        private final boolean[] mapPinned = {false};
        private final ActivityResultLauncher<String> idPickerLauncher =
            registerForActivityResult(new ActivityResultContracts.GetContent(), uri -> {
                if (uri == null) {
                    return;
                }
                selectedIdUri = uri;
                if (selectedIdText != null) {
                    String fileName = resolveFileName(uri);
                    selectedIdText.setText(fileName == null || fileName.isEmpty() ? "ID selected" : fileName);
                }
            });

        @Override
        public View onCreateView(LayoutInflater inflater, android.view.ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_register, container, false);
            EditText fullName = view.findViewById(R.id.register_full_name);
            EditText email = view.findViewById(R.id.register_email);
            EditText password = view.findViewById(R.id.register_password);
            EditText confirmPassword = view.findViewById(R.id.register_confirm_password);
            EditText phone = view.findViewById(R.id.register_phone);
            EditText street = view.findViewById(R.id.register_street);
            EditText postalCode = view.findViewById(R.id.register_postal_code);
            Spinner countrySpinner = view.findViewById(R.id.register_country_spinner);
            provinceSpinner = view.findViewById(R.id.register_province_spinner);
            citySpinner = view.findViewById(R.id.register_city_spinner);
            barangaySpinner = view.findViewById(R.id.register_barangay_spinner);
            selectedIdText = view.findViewById(R.id.text_selected_id);
            TextView registerInlineError = view.findViewById(R.id.register_inline_error);
            Button chooseIdButton = view.findViewById(R.id.btn_choose_id);
            Button createButton = view.findViewById(R.id.btn_create_account);
            Button backToLoginButton = view.findViewById(R.id.btn_back_to_login);
            Button useLocationButton = view.findViewById(R.id.btn_register_use_location);
            TextView mapStatus = view.findViewById(R.id.register_map_status);
            WebView mapWebView = view.findViewById(R.id.register_map_webview);

            setupAddressMappings();
            bindSpinner(countrySpinner, Arrays.asList("Philippines"));
            bindSpinner(provinceSpinner, Arrays.asList("South Cotabato"));
            setupAddressAutoSelect();
            chooseIdButton.setOnClickListener(v -> idPickerLauncher.launch("image/*"));

            MainActivity activity = (MainActivity) requireActivity();
            activity.setupDeliveryMapWebView(mapWebView, mapStatus, deliveryLat, deliveryLng, mapPinned);
            useLocationButton.setOnClickListener(v -> activity.fetchCheckoutCurrentLocation(
                mapWebView,
                deliveryLat,
                deliveryLng,
                mapPinned,
                mapStatus,
                street,
                provinceSpinner,
                citySpinner,
                barangaySpinner,
                postalCode
            ));

            createButton.setOnClickListener(v -> {
                registerInlineError.setVisibility(View.GONE);
                String fullNameValue = fullName.getText().toString().trim();
                String emailValue = email.getText().toString().trim();
                String passwordValue = password.getText().toString().trim();
                String confirmPasswordValue = confirmPassword.getText().toString().trim();
                String phoneValue = phone.getText().toString().trim();
                String streetValue = street.getText().toString().trim();
                String postalCodeValue = postalCode.getText().toString().trim();
                String cityValue = citySpinner.getSelectedItem() == null ? "" : citySpinner.getSelectedItem().toString().trim();
                String barangayValue = barangaySpinner.getSelectedItem() == null ? "" : barangaySpinner.getSelectedItem().toString().trim();

                if (fullNameValue.isEmpty() || emailValue.isEmpty() || passwordValue.isEmpty() || confirmPasswordValue.isEmpty()) {
                    if (fullNameValue.isEmpty()) fullName.setError("Required");
                    if (emailValue.isEmpty()) email.setError("Required");
                    if (passwordValue.isEmpty()) password.setError("Required");
                    if (confirmPasswordValue.isEmpty()) confirmPassword.setError("Required");
                    registerInlineError.setText("Complete all required fields.");
                    registerInlineError.setVisibility(View.VISIBLE);
                    return;
                }
                if (phoneValue.isEmpty() || streetValue.isEmpty() || postalCodeValue.isEmpty()) {
                    if (phoneValue.isEmpty()) phone.setError("Required");
                    if (streetValue.isEmpty()) street.setError("Required");
                    if (postalCodeValue.isEmpty()) postalCode.setError("Required");
                    registerInlineError.setText("Contact and address fields are required.");
                    registerInlineError.setVisibility(View.VISIBLE);
                    return;
                }
                if (!Patterns.EMAIL_ADDRESS.matcher(emailValue).matches()) {
                    email.setError("Invalid email");
                    registerInlineError.setText("Enter a valid email address.");
                    registerInlineError.setVisibility(View.VISIBLE);
                    return;
                }
                if (phoneValue.length() < 10) {
                    phone.setError("Invalid phone");
                    registerInlineError.setText("Enter a valid phone number.");
                    registerInlineError.setVisibility(View.VISIBLE);
                    return;
                }
                if (postalCodeValue.length() < 4) {
                    postalCode.setError("Invalid postal code");
                    registerInlineError.setText("Enter a valid postal code.");
                    registerInlineError.setVisibility(View.VISIBLE);
                    return;
                }
                if (cityValue.isEmpty() || barangayValue.isEmpty()) {
                    registerInlineError.setText("Please select city and barangay.");
                    registerInlineError.setVisibility(View.VISIBLE);
                    return;
                }
                if (passwordValue.length() < 8) {
                    password.setError("At least 8 characters");
                    registerInlineError.setText("Password must be at least 8 characters.");
                    registerInlineError.setVisibility(View.VISIBLE);
                    return;
                }
                if (!passwordValue.equals(confirmPasswordValue)) {
                    confirmPassword.setError("Password mismatch");
                    registerInlineError.setText("Passwords do not match.");
                    registerInlineError.setVisibility(View.VISIBLE);
                    return;
                }
                if (!mapPinned[0]) {
                    registerInlineError.setText("Pin your delivery location on the map (tap map or use current location).");
                    registerInlineError.setVisibility(View.VISIBLE);
                    return;
                }
                createButton.setEnabled(false);
                createButton.setText("Creating...");
                activity.registerWithServer(
                    fullNameValue,
                    emailValue,
                    passwordValue,
                    phoneValue,
                    streetValue,
                    cityValue,
                    barangayValue,
                    postalCodeValue,
                    deliveryLat[0],
                    deliveryLng[0],
                    new SimpleCallback() {
                        @Override
                        public void onSuccess(String message) {
                            Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                            activity.loadFragment(new LoginFragment());
                        }

                        @Override
                        public void onError(String message) {
                            createButton.setEnabled(true);
                            createButton.setText("Create Account");
                            registerInlineError.setText(message);
                            registerInlineError.setVisibility(View.VISIBLE);
                        }
                    }
                );
            });
            backToLoginButton.setOnClickListener(v ->
                ((MainActivity) requireActivity()).loadFragment(new LoginFragment()));
            return view;
        }

        private String resolveFileName(Uri uri) {
            Cursor cursor = requireContext().getContentResolver().query(uri, null, null, null, null);
            if (cursor == null) {
                return uri.getLastPathSegment();
            }
            try {
                int nameIndex = cursor.getColumnIndex(OpenableColumns.DISPLAY_NAME);
                if (nameIndex >= 0 && cursor.moveToFirst()) {
                    return cursor.getString(nameIndex);
                }
                return uri.getLastPathSegment();
            } finally {
                cursor.close();
            }
        }

        private void setupAddressMappings() {
            provinceCityMap.clear();
            cityBarangayMap.clear();

            provinceCityMap.put("South Cotabato", Arrays.asList("General Santos City"));

            cityBarangayMap.put("General Santos City", Arrays.asList(
                "Apopong", "Baluan", "Batomelong", "Buayan", "Bula", "Calumpang",
                "City Heights", "Conel", "Dadiangas East", "Dadiangas North",
                "Dadiangas South", "Dadiangas West", "Fatima", "Katangawan",
                "Labangal", "Lagao", "Ligaya", "Mabuhay", "Olympog", "San Isidro",
                "San Jose", "Siguel", "Sinawal", "Tambler", "Tinagacan", "Upper Labay"
            ));
        }

        private void setupAddressAutoSelect() {
            provinceSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
                @Override
                public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                    String province = parent.getItemAtPosition(position).toString();
                    List<String> cities = provinceCityMap.get(province);
                    if (cities == null || cities.isEmpty()) {
                        cities = Arrays.asList("General Santos City");
                    }
                    bindSpinner(citySpinner, cities);
                    citySpinner.setSelection(0);
                    updateBarangayByCity(cities.get(0));
                }

                @Override
                public void onNothingSelected(AdapterView<?> parent) {}
            });

            citySpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
                @Override
                public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                    String city = parent.getItemAtPosition(position).toString();
                    updateBarangayByCity(city);
                }

                @Override
                public void onNothingSelected(AdapterView<?> parent) {}
            });

            if (provinceSpinner.getAdapter() != null && provinceSpinner.getAdapter().getCount() > 0) {
                provinceSpinner.setSelection(0);
            }
        }

        private void updateBarangayByCity(String city) {
            List<String> barangays = cityBarangayMap.get(city);
            if (barangays == null || barangays.isEmpty()) {
                barangays = Arrays.asList("Poblacion");
            }
            bindSpinner(barangaySpinner, barangays);
            barangaySpinner.setSelection(0);
        }

        private void bindSpinner(Spinner spinner, List<String> options) {
            ArrayAdapter<String> adapter = new ArrayAdapter<>(
                requireContext(),
                R.layout.item_spinner_selected_small,
                options
            );
            adapter.setDropDownViewResource(R.layout.item_spinner_dropdown_small);
            spinner.setAdapter(adapter);
        }
    }

    public static class HomeFragment extends Fragment {
        private static final int[] PRODUCT_CARD_IDS = {
            R.id.product_black_elite_v2,
            R.id.product_black_elite_v1,
            R.id.product_black_question_v2,
            R.id.product_crsm_elite,
            R.id.product_vapor_zero,
            R.id.product_uotofo,
            R.id.product_xvape_slimbar,
            R.id.product_kalo_v2,
            R.id.product_minican,
            R.id.product_black_v1_battery,
            R.id.product_xvape_slimbar_device,
            R.id.product_pod_formula,
            R.id.product_storm,
            R.id.product_bl_ck,
            R.id.product_vi_bar
        };

        private static final String[] PRODUCT_SEARCH_NAMES = {
            "BLACK ELITE V2",
            "BLACK ELITE V1",
            "BLACK? V2",
            "CRYSM ELITE",
            "VAPOR ZERO",
            "UOTOFO",
            "XVAPE SLIMBAR",
            "KALO V2",
            "MINICAN",
            "BLACK V1 BATTERY",
            "X-VAPE SLIMBAR DEVICE",
            "POD FORMULA",
            "STORM",
            "BL?CK",
            "VI BAR"
        };

        @Override
        public View onCreateView(LayoutInflater inflater, android.view.ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_home, container, false);
            setupHeaderActions(view);
            setupProductClickListeners(view);
            applyProductsFromServer(view);
            return view;
        }

        @Override
        public void onResume() {
            super.onResume();
            MainActivity activity = (MainActivity) requireActivity();
            activity.pollSupportUnreadCount();
            activity.refreshMessageBadges();
        }

        private void setupHeaderActions(View view) {
            MainActivity activity = (MainActivity) requireActivity();
            View loginAction = view.findViewById(R.id.action_login);
            loginAction.setVisibility(activity.isUserLoggedIn() ? View.GONE : View.VISIBLE);
            loginAction.setOnClickListener(v ->
                activity.loadFragment(new LoginFragment()));
            view.findViewById(R.id.action_search).setOnClickListener(v ->
                showSearchDialog(view));
            view.findViewById(R.id.action_messages).setOnClickListener(v ->
                activity.loadFragment(MessagesFragment.newInstance("", "", false)));
            view.findViewById(R.id.action_notifications).setOnClickListener(v ->
                activity.loadFragment(new NotificationsFragment()));
        }

        private void showSearchDialog(View rootView) {
            EditText searchInput = new EditText(requireContext());
            searchInput.setHint("Search products...");
            int pad = dpToPx(12);
            searchInput.setPadding(pad, pad, pad, pad);

            AlertDialog dialog = new AlertDialog.Builder(requireContext())
                .setTitle("Search Products")
                .setView(searchInput)
                .setNegativeButton("Close", null)
                .setNeutralButton("Reset", null)
                .setPositiveButton("Search", null)
                .create();

            dialog.setOnShowListener(d -> {
                dialog.getButton(AlertDialog.BUTTON_NEUTRAL).setOnClickListener(v -> {
                    resetProductFilter(rootView);
                    Toast.makeText(requireContext(), "Showing all products", Toast.LENGTH_SHORT).show();
                });
                dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(v -> {
                    String query = searchInput.getText().toString().trim();
                    if (query.isEmpty()) {
                        Toast.makeText(requireContext(), "Type a keyword to search", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    int matches = filterProducts(rootView, query);
                    Toast.makeText(
                        requireContext(),
                        matches > 0 ? "Found " + matches + " product(s)" : "No products found for \"" + query + "\"",
                        Toast.LENGTH_SHORT
                    ).show();
                });
            });
            dialog.show();
        }

        private int filterProducts(View rootView, String query) {
            int matches = 0;
            String keyword = query.toLowerCase(Locale.US);
            for (int i = 0; i < PRODUCT_CARD_IDS.length; i++) {
                View card = rootView.findViewById(PRODUCT_CARD_IDS[i]);
                if (card == null) {
                    continue;
                }
                boolean visible = PRODUCT_SEARCH_NAMES[i].toLowerCase(Locale.US).contains(keyword);
                card.setVisibility(visible ? View.VISIBLE : View.GONE);
                if (visible) {
                    matches++;
                }
            }
            return matches;
        }

        private void resetProductFilter(View rootView) {
            for (int cardId : PRODUCT_CARD_IDS) {
                View card = rootView.findViewById(cardId);
                if (card != null) {
                    card.setVisibility(View.VISIBLE);
                }
            }
        }

        private int dpToPx(int dp) {
            float density = requireContext().getResources().getDisplayMetrics().density;
            return Math.round(dp * density);
        }

        private void setupProductClickListeners(View view) {
            view.findViewById(R.id.product_black_elite_v2).setOnClickListener(v ->
                openProductDetail("BLACK ELITE V2", R.drawable.black_elite_v2));
            view.findViewById(R.id.product_black_elite_v1).setOnClickListener(v ->
                openProductDetail("BLACK ELITE V1", R.drawable.black_elite_v1));
            view.findViewById(R.id.product_black_question_v2).setOnClickListener(v ->
                openProductDetail("BLACK? V2", R.drawable.black_question_v2));
            view.findViewById(R.id.product_crsm_elite).setOnClickListener(v ->
                openProductDetail("CRYSM ELITE", R.drawable.crysm_elite));
            view.findViewById(R.id.product_vapor_zero).setOnClickListener(v ->
                openProductDetail("VAPOR ZERO", R.drawable.vapor_zero));
            view.findViewById(R.id.product_uotofo).setOnClickListener(v ->
                openProductDetail("UOTOFO", R.drawable.uotofo));
            view.findViewById(R.id.product_xvape_slimbar).setOnClickListener(v ->
                openProductDetail("XVAPE SLIMBAR", R.drawable.xvape_slimbar));
            view.findViewById(R.id.product_kalo_v2).setOnClickListener(v ->
                openProductDetail("KALO V2", R.drawable.kalo_v2));
            view.findViewById(R.id.product_minican).setOnClickListener(v ->
                openProductDetail("MINICAN", R.drawable.minican));
            view.findViewById(R.id.product_black_v1_battery).setOnClickListener(v ->
                openProductDetail("BLACK V1 BATTERY", R.drawable.black_v1_battery));
            view.findViewById(R.id.product_xvape_slimbar_device).setOnClickListener(v ->
                openProductDetail("X-VAPE SLIMBAR DEVICE", R.drawable.xvape_slimbar_device));
            view.findViewById(R.id.product_pod_formula).setOnClickListener(v ->
                openProductDetail("POD FORMULA", R.drawable.pod_formula));
            view.findViewById(R.id.product_storm).setOnClickListener(v ->
                openProductDetail("STORM", R.drawable.storm));
            view.findViewById(R.id.product_bl_ck).setOnClickListener(v ->
                openProductDetail("BL?CK", R.drawable.bl_ck));
            view.findViewById(R.id.product_vi_bar).setOnClickListener(v ->
                openProductDetail("VI BAR", R.drawable.vi_bar));

            setupAddToCartButtons(view);
        }

        private void setupAddToCartButtons(View view) {
            setupAddButton(view, R.id.btn_add_cart_black_elite_v2, "BLACK ELITE V2");
            setupAddButton(view, R.id.btn_add_cart_black_elite_v1, "BLACK ELITE V1");
            setupAddButton(view, R.id.btn_add_cart_black_question_v2, "BLACK? V2");
            setupAddButton(view, R.id.btn_add_cart_crysm_elite, "CRYSM ELITE");
            setupAddButton(view, R.id.btn_add_cart_vapor_zero, "VAPOR ZERO");
            setupAddButton(view, R.id.btn_add_cart_uotofo, "UOTOFO");
            setupAddButton(view, R.id.btn_add_cart_xvape_slimbar, "XVAPE SLIMBAR");
            setupAddButton(view, R.id.btn_add_cart_kalo_v2, "KALO V2");
            setupAddButton(view, R.id.btn_add_cart_minican, "MINICAN");
            setupAddButton(view, R.id.btn_add_cart_black_v1_battery, "BLACK V1 BATTERY");
            setupAddButton(view, R.id.btn_add_cart_xvape_slimbar_device, "X-VAPE SLIMBAR DEVICE");
            setupAddButton(view, R.id.btn_add_cart_pod_formula, "POD FORMULA");
            setupAddButton(view, R.id.btn_add_cart_storm, "STORM");
            setupAddButton(view, R.id.btn_add_cart_bl_ck, "BL?CK");
            setupAddButton(view, R.id.btn_add_cart_vi_bar, "VI BAR");
        }

        private void setupAddButton(View parentView, int buttonId, String name) {
            parentView.findViewById(buttonId).setOnClickListener(v -> {
                MainActivity activity = (MainActivity) requireActivity();
                ProductCatalogEntry product = activity.getProductByName(name);
                if (product == null) {
                    Toast.makeText(requireContext(), "Loading product info, please try again", Toast.LENGTH_SHORT).show();
                    return;
                }
                activity.showAddToCartFlow(this, product);
            });
        }

        private void applyProductsFromServer(View view) {
            MainActivity activity = (MainActivity) requireActivity();
            activity.fetchProductsFromServer(new ProductsCallback() {
                @Override
                public void onSuccess(Map<String, ProductCatalogEntry> productsByName) {
                    updateCard(view, R.id.product_black_elite_v2, "BLACK ELITE V2", productsByName);
                    updateCard(view, R.id.product_black_elite_v1, "BLACK ELITE V1", productsByName);
                    updateCard(view, R.id.product_black_question_v2, "BLACK? V2", productsByName);
                    updateCard(view, R.id.product_crsm_elite, "CRYSM ELITE", productsByName);
                    updateCard(view, R.id.product_vapor_zero, "VAPOR ZERO", productsByName);
                    updateCard(view, R.id.product_uotofo, "UOTOFO", productsByName);
                    updateCard(view, R.id.product_xvape_slimbar, "XVAPE SLIMBAR", productsByName);
                    updateCard(view, R.id.product_kalo_v2, "KALO V2", productsByName);
                    updateCard(view, R.id.product_minican, "MINICAN", productsByName);
                    updateCard(view, R.id.product_black_v1_battery, "BLACK V1 BATTERY", productsByName);
                    updateCard(view, R.id.product_xvape_slimbar_device, "X-VAPE SLIMBAR DEVICE", productsByName);
                    updateCard(view, R.id.product_pod_formula, "POD FORMULA", productsByName);
                    updateCard(view, R.id.product_storm, "STORM", productsByName);
                    updateCard(view, R.id.product_bl_ck, "BL?CK", productsByName);
                    updateCard(view, R.id.product_vi_bar, "VI BAR", productsByName);
                }

                @Override
                public void onError(String message) {
                    Toast.makeText(requireContext(), "Products sync failed", Toast.LENGTH_SHORT).show();
                }
            });
        }

        private void updateCard(View root, int cardId, String productName, Map<String, ProductCatalogEntry> productsByName) {
            ProductCatalogEntry product = productsByName.get(productName.toUpperCase(Locale.US));
            if (product == null) {
                return;
            }
            View cardView = root.findViewById(cardId);
            if (!(cardView instanceof androidx.cardview.widget.CardView)) {
                return;
            }
            LinearLayout content = (LinearLayout) ((androidx.cardview.widget.CardView) cardView).getChildAt(0);
            if (content == null || content.getChildCount() < 5) {
                return;
            }

            TextView nameText = (TextView) content.getChildAt(1);
            TextView priceText = (TextView) content.getChildAt(2);
            TextView specText = (TextView) content.getChildAt(3);
            nameText.setText(product.name);
            priceText.setText(String.format(Locale.US, "₱%.2f", product.price));
            specText.setText(product.spec + " • " + product.formatStockLabel());
        }

        private void openProductDetail(String productName, int imageResId) {
            MainActivity activity = (MainActivity) requireActivity();
            ProductCatalogEntry product = activity.getProductByName(productName);
            if (product != null) {
                showProductDetail(product, imageResId);
                return;
            }
            activity.fetchProductsFromServer(new ProductsCallback() {
                @Override
                public void onSuccess(Map<String, ProductCatalogEntry> productsByName) {
                    ProductCatalogEntry loaded = productsByName.get(productName.toUpperCase(Locale.US));
                    if (loaded != null) {
                        showProductDetail(loaded, imageResId);
                    } else {
                        Toast.makeText(requireContext(), "Product not found", Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onError(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                }
            });
        }

        private void showProductDetail(ProductCatalogEntry product, int imageResId) {
            ProductDetailFragment detailFragment = new ProductDetailFragment();
            Bundle args = new Bundle();
            args.putInt("productId", product.id);
            args.putInt("productImageRes", imageResId);
            detailFragment.setArguments(args);
            ((MainActivity) requireActivity()).loadFragment(detailFragment);
        }
    }

    public static class SearchFragment extends Fragment {
        private final String[] productNames = {
            "BLACK ELITE V2", "BLACK ELITE V1", "BLACK? V2", "CRYSM ELITE",
            "VAPOR ZERO", "UOTOFO", "XVAPE SLIMBAR", "KALO V2",
            "MINICAN", "BLACK V1 BATTERY", "X-VAPE SLIMBAR DEVICE",
            "POD FORMULA", "STORM", "BL?CK", "VI BAR"
        };

        @Override
        public View onCreateView(LayoutInflater inflater, android.view.ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_search, container, false);
            EditText input = view.findViewById(R.id.search_input);
            TextView result = view.findViewById(R.id.search_result_text);
            Button searchButton = view.findViewById(R.id.btn_search_products);

            view.findViewById(R.id.btn_back_from_search).setOnClickListener(v ->
                ((MainActivity) requireActivity()).loadFragment(new HomeFragment()));

            searchButton.setOnClickListener(v -> {
                String keyword = input.getText().toString().trim().toLowerCase();
                if (keyword.isEmpty()) {
                    Toast.makeText(requireContext(), "Type a keyword to search", Toast.LENGTH_SHORT).show();
                    return;
                }

                StringBuilder matches = new StringBuilder();
                int count = 0;
                for (String product : productNames) {
                    if (product.toLowerCase().contains(keyword)) {
                        count++;
                        matches.append("• ").append(product).append("\n");
                    }
                }

                if (count == 0) {
                    result.setText("No products found for \"" + keyword + "\"");
                    return;
                }
                result.setText("Found " + count + " product(s):\n\n" + matches);
            });

            return view;
        }
    }

    public static class MessagesFragment extends Fragment {
        private static final String ARG_RELATED_ORDER = "related_order";
        private static final String ARG_RELATED_PRODUCT = "related_product";
        private static final String ARG_LOCK_RELATED_ORDER = "lock_related_order";

        private final Handler messagesRefreshHandler = new Handler(Looper.getMainLooper());
        private Runnable messagesRefreshRunnable;
        private boolean messagesMarkedReadOnce = false;
        private LinearLayout threadContainer;
        private androidx.core.widget.NestedScrollView messagesScroll;
        private TextView messagesStatus;
        private int lastRenderedMessageCount = -1;
        private final Set<Integer> renderedMessageIds = new HashSet<>();

        static MessagesFragment newInstance(String relatedOrder, String relatedProduct, boolean lockRelatedOrder) {
            MessagesFragment fragment = new MessagesFragment();
            Bundle args = new Bundle();
            args.putString(ARG_RELATED_ORDER, relatedOrder == null ? "" : relatedOrder);
            args.putString(ARG_RELATED_PRODUCT, relatedProduct == null ? "" : relatedProduct);
            args.putBoolean(ARG_LOCK_RELATED_ORDER, lockRelatedOrder);
            fragment.setArguments(args);
            return fragment;
        }

        @Override
        public View onCreateView(LayoutInflater inflater, android.view.ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_messages, container, false);
            MainActivity activity = (MainActivity) requireActivity();
            Bundle args = getArguments();
            String preselectedOrder = args != null ? args.getString(ARG_RELATED_ORDER, "") : "";
            String preselectedProduct = args != null ? args.getString(ARG_RELATED_PRODUCT, "") : "";
            boolean lockRelatedOrder = args != null && args.getBoolean(ARG_LOCK_RELATED_ORDER, false);
            messagesStatus = view.findViewById(R.id.messages_status);
            threadContainer = view.findViewById(R.id.messages_thread_container);
            EditText input = view.findViewById(R.id.messages_input);
            messagesScroll = view.findViewById(R.id.messages_scroll);
            Spinner relatedOrderSpinner = view.findViewById(R.id.messages_related_order_spinner);
            final List<ChatOrderOption> relatedOrderOptions = new ArrayList<>();
            relatedOrderOptions.add(new ChatOrderOption(0, "", ""));
            ArrayAdapter<ChatOrderOption> relatedOrderAdapter = new ArrayAdapter<>(
                requireContext(),
                android.R.layout.simple_spinner_item,
                relatedOrderOptions
            );
            relatedOrderAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
            relatedOrderSpinner.setAdapter(relatedOrderAdapter);
            if (lockRelatedOrder) {
                relatedOrderSpinner.setEnabled(false);
                relatedOrderSpinner.setAlpha(0.75f);
            }

            view.findViewById(R.id.btn_back_from_messages).setOnClickListener(v ->
                ((MainActivity) requireActivity()).loadFragment(new HomeFragment()));

            activity.fetchOrdersFromServer(new OrdersCallback() {
                @Override
                public void onSuccess(List<OrderInfo> orders) {
                    relatedOrderOptions.clear();
                    relatedOrderOptions.add(new ChatOrderOption(0, "", ""));
                    int targetSelection = 0;
                    for (OrderInfo order : orders) {
                        String reference = order.referenceNumber == null ? "" : order.referenceNumber;
                        String productSummary = order.itemsSummary == null ? "" : order.itemsSummary;
                        if (!preselectedOrder.isEmpty() && preselectedOrder.equalsIgnoreCase(reference)) {
                            productSummary = preselectedProduct.isEmpty() ? productSummary : preselectedProduct;
                        }
                        relatedOrderOptions.add(new ChatOrderOption(order.orderId, reference, productSummary));
                        if (!preselectedOrder.isEmpty() && preselectedOrder.equalsIgnoreCase(reference)) {
                            targetSelection = relatedOrderOptions.size() - 1;
                        }
                    }
                    if (!preselectedOrder.isEmpty() && targetSelection == 0) {
                        relatedOrderOptions.add(new ChatOrderOption(0, preselectedOrder, preselectedProduct));
                        targetSelection = relatedOrderOptions.size() - 1;
                    }
                    relatedOrderAdapter.notifyDataSetChanged();
                    relatedOrderSpinner.setSelection(targetSelection);
                }

                @Override
                public void onError(String message) {
                    // keep default "No specific order" option
                }
            });

            Button sendButton = view.findViewById(R.id.btn_send_message);
            LinearLayout quickActions = view.findViewById(R.id.messages_quick_actions);
            setupMessageQuickActions(quickActions, input, () -> sendButton.performClick());

            sendButton.setOnClickListener(v -> {
                String message = input.getText() == null ? "" : input.getText().toString().trim();
                if (message.isEmpty()) {
                    Toast.makeText(requireContext(), "Type a message first", Toast.LENGTH_SHORT).show();
                    return;
                }
                ChatOrderOption selected = (ChatOrderOption) relatedOrderSpinner.getSelectedItem();
                int relatedOrderId = selected != null ? selected.orderId : 0;
                String relatedOrderRef = selected != null ? selected.reference : "";
                String relatedProduct = selected != null ? selected.productSummary : "";
                sendButton.setEnabled(false);
                activity.sendSupportMessageToServer(message, relatedOrderId, relatedOrderRef, relatedProduct, new SimpleCallback() {
                    @Override
                    public void onSuccess(String response) {
                        sendButton.setEnabled(true);
                        input.setText("");
                        messagesStatus.setText(response);
                        loadMessages(activity, true);
                    }

                    @Override
                    public void onError(String message) {
                        sendButton.setEnabled(true);
                        messagesStatus.setText(message);
                        Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                    }
                });
            });

            return view;
        }

        @Override
        public void onResume() {
            super.onResume();
            messagesMarkedReadOnce = false;
            lastRenderedMessageCount = -1;
            renderedMessageIds.clear();
            startMessagesAutoRefresh();
        }

        @Override
        public void onPause() {
            stopMessagesAutoRefresh();
            super.onPause();
        }

        @Override
        public void onDestroyView() {
            stopMessagesAutoRefresh();
            threadContainer = null;
            messagesScroll = null;
            messagesStatus = null;
            super.onDestroyView();
        }

        private void startMessagesAutoRefresh() {
            stopMessagesAutoRefresh();
            MainActivity activity = (MainActivity) requireActivity();
            messagesRefreshRunnable = () -> {
                if (!isAdded() || threadContainer == null) {
                    return;
                }
                boolean markRead = !messagesMarkedReadOnce;
                loadMessages(activity, markRead);
                messagesMarkedReadOnce = true;
                messagesRefreshHandler.postDelayed(messagesRefreshRunnable, MESSAGE_AUTO_REFRESH_MS);
            };
            messagesRefreshHandler.post(messagesRefreshRunnable);
        }

        private void stopMessagesAutoRefresh() {
            if (messagesRefreshRunnable != null) {
                messagesRefreshHandler.removeCallbacks(messagesRefreshRunnable);
                messagesRefreshRunnable = null;
            }
        }

        private void setupMessageQuickActions(LinearLayout container, EditText input, Runnable onSend) {
            String[][] chips = {
                {"Order status", "What is my order status?"},
                {"Delivery", "Where is my delivery?"},
                {"Human support", "human support"}
            };
            for (String[] chip : chips) {
                Button chipButton = new Button(requireContext());
                chipButton.setText(chip[0]);
                chipButton.setAllCaps(false);
                chipButton.setTextSize(TypedValue.COMPLEX_UNIT_SP, 12f);
                chipButton.setTextColor(0xFF0F766E);
                chipButton.setBackgroundColor(0xFFECFDF5);
                chipButton.setPadding(dpToPx(12), dpToPx(6), dpToPx(12), dpToPx(6));
                LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                    ViewGroup.LayoutParams.WRAP_CONTENT,
                    ViewGroup.LayoutParams.WRAP_CONTENT
                );
                params.setMarginEnd(dpToPx(8));
                chipButton.setOnClickListener(v -> {
                    input.setText(chip[1]);
                    onSend.run();
                });
                container.addView(chipButton, params);
            }
        }

        private void loadMessages(MainActivity activity, boolean markRead) {
            if (threadContainer == null || messagesScroll == null || messagesStatus == null) {
                return;
            }
            activity.fetchSupportMessagesFromServer(markRead, new MessagesCallback() {
                @Override
                public void onSuccess(List<SupportMessage> messages) {
                    if (!isAdded() || threadContainer == null) {
                        return;
                    }
                    renderMessageThread(messages);
                    messagesScroll.post(() -> messagesScroll.fullScroll(View.FOCUS_DOWN));
                }

                @Override
                public void onError(String message) {
                    if (!isAdded() || threadContainer == null) {
                        return;
                    }
                    if (threadContainer.getChildCount() == 0) {
                        addMessageBubble(
                            threadContainer,
                            0,
                            "System",
                            message,
                            "system",
                            "system",
                            false,
                            ""
                        );
                    }
                    messagesStatus.setText("Could not load messages.");
                }
            });
        }

        private void renderMessageThread(List<SupportMessage> messages) {
            if (threadContainer == null) {
                return;
            }
            if (messages.isEmpty()) {
                if (threadContainer.getChildCount() == 0) {
                    addMessageBubble(
                        threadContainer,
                        0,
                        "Chatbot",
                        "Hi! Ask about order status, delivery, or payments. Type \"human support\" to reach an admin.",
                        "chatbot",
                        "text",
                        false,
                        ""
                    );
                    messagesStatus.setText("Start chatting below.");
                }
                lastRenderedMessageCount = 0;
                return;
            }

            Set<Integer> incomingIds = new HashSet<>();
            for (SupportMessage item : messages) {
                if (item.id > 0) {
                    incomingIds.add(item.id);
                }
            }
            boolean hasStableIds = !incomingIds.isEmpty();
            if (hasStableIds) {
                if (!incomingIds.equals(renderedMessageIds)) {
                    threadContainer.removeAllViews();
                    renderedMessageIds.clear();
                }
            } else if (messages.size() != lastRenderedMessageCount) {
                threadContainer.removeAllViews();
                renderedMessageIds.clear();
            } else {
                return;
            }

            boolean addedMessage = false;
            for (SupportMessage item : messages) {
                if (hasStableIds && item.id > 0 && renderedMessageIds.contains(item.id)) {
                    continue;
                }
                String roleKey = resolveMessageRoleKey(item);
                addMessageBubble(
                    threadContainer,
                    item.id,
                    item.senderName,
                    item.messageBody,
                    roleKey,
                    item.messageType,
                    item.fromCustomer,
                    item.createdAt
                );
                if (item.id > 0) {
                    renderedMessageIds.add(item.id);
                }
                addedMessage = true;
            }

            if (addedMessage) {
                messagesStatus.setText("Connected to QuickPuff support.");
                lastRenderedMessageCount = messages.size();
            }
        }

        private String resolveMessageRoleKey(SupportMessage item) {
            if ("system".equals(item.messageType) || "system".equals(item.senderRole)) {
                return "system";
            }
            if ("chatbot".equals(item.senderRole) || "bot".equals(item.senderRole)) {
                return "chatbot";
            }
            if (item.fromCustomer || "customer".equals(item.senderRole)) {
                return "customer";
            }
            if ("rider".equals(item.senderRole)) {
                return "rider";
            }
            if ("admin".equals(item.senderRole) || "staff".equals(item.senderRole)) {
                return "admin";
            }
            return "admin";
        }

        private void addMessageBubble(
            LinearLayout parent,
            int messageId,
            String sender,
            String message,
            String roleKey,
            String messageType,
            boolean isUser,
            String createdAt
        ) {
            View row = LayoutInflater.from(requireContext()).inflate(R.layout.item_message_bubble, parent, false);
            if (messageId > 0) {
                row.setTag(messageId);
            }
            View bubbleWrap = row.findViewById(R.id.message_bubble_wrap);
            TextView senderView = row.findViewById(R.id.message_sender);
            TextView bodyView = row.findViewById(R.id.message_body);
            TextView timeView = row.findViewById(R.id.message_time);

            senderView.setText(sender);
            bodyView.setText(message);
            if (createdAt != null && !createdAt.trim().isEmpty()) {
                timeView.setText(createdAt);
                timeView.setVisibility(View.VISIBLE);
            } else {
                timeView.setVisibility(View.GONE);
            }

            boolean isSystem = "system".equals(roleKey) || "system".equals(messageType);
            int bgRes = R.drawable.bg_message_incoming;
            if (isUser || "customer".equals(roleKey)) {
                bgRes = R.drawable.bg_message_outgoing;
            } else if (isSystem) {
                bgRes = R.drawable.bg_message_system;
            } else if ("chatbot".equals(roleKey)) {
                bgRes = R.drawable.bg_message_bot;
            } else if ("rider".equals(roleKey)) {
                bgRes = R.drawable.bg_message_rider;
            } else if ("admin".equals(roleKey)) {
                bgRes = R.drawable.bg_message_admin;
            }
            bubbleWrap.setBackgroundResource(bgRes);

            LinearLayout.LayoutParams bubbleParams = new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.WRAP_CONTENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            );
            if (isSystem) {
                bubbleParams.gravity = Gravity.CENTER_HORIZONTAL;
                bubbleParams.width = ViewGroup.LayoutParams.WRAP_CONTENT;
            } else if (isUser || "customer".equals(roleKey)) {
                bubbleParams.gravity = Gravity.END;
            } else {
                bubbleParams.gravity = Gravity.START;
            }
            bubbleWrap.setLayoutParams(bubbleParams);

            LinearLayout.LayoutParams rowParams = new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            );
            row.setLayoutParams(rowParams);
            parent.addView(row);
        }

        private int dpToPx(int dp) {
            return Math.round(dp * requireContext().getResources().getDisplayMetrics().density);
        }
    }

    public static class NotificationsFragment extends Fragment {
        @Override
        public View onCreateView(LayoutInflater inflater, android.view.ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_notifications, container, false);
            TextView status = view.findViewById(R.id.notifications_status);

            view.findViewById(R.id.btn_back_from_notifications).setOnClickListener(v ->
                ((MainActivity) requireActivity()).loadFragment(new HomeFragment()));

            view.findViewById(R.id.btn_clear_notifications).setOnClickListener(v -> {
                status.setText("No new notifications.");
                Toast.makeText(requireContext(), "Notifications cleared", Toast.LENGTH_SHORT).show();
            });

            return view;
        }
    }

    public static class MyPurchaseFragment extends Fragment {
        private static final LinkedHashMap<String, String> PURCHASE_TABS = new LinkedHashMap<>();

        static {
            PURCHASE_TABS.put("all", "All");
            PURCHASE_TABS.put("to_pay", "To Pay");
            PURCHASE_TABS.put("to_ship", "To Ship");
            PURCHASE_TABS.put("to_receive", "To Receive");
            PURCHASE_TABS.put("completed", "Completed");
            PURCHASE_TABS.put("to_review", "To Review");
            PURCHASE_TABS.put("cancelled", "Cancelled");
            PURCHASE_TABS.put("return_refund", "Return/Refund");
            PURCHASE_TABS.put("failed_delivery", "Failed Delivery");
        }

        private final List<OrderInfo> allOrders = new ArrayList<>();
        private String selectedTab = "all";
        private LinearLayout filterTabsContainer;
        private LinearLayout ordersContainer;
        private TextView inlineError;
        private LayoutInflater layoutInflater;

        @Override
        public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_my_purchase, container, false);
            MainActivity activity = (MainActivity) requireActivity();
            layoutInflater = inflater;
            filterTabsContainer = view.findViewById(R.id.purchase_filter_tabs);
            ordersContainer = view.findViewById(R.id.purchase_orders_container);
            inlineError = view.findViewById(R.id.my_purchase_inline_error);

            setupFilterTabs();

            activity.fetchOrdersFromServer(new OrdersCallback() {
                @Override
                public void onSuccess(List<OrderInfo> orders) {
                    inlineError.setVisibility(View.GONE);
                    allOrders.clear();
                    allOrders.addAll(orders);
                    refreshPurchaseUi();
                }

                @Override
                public void onError(String message) {
                    inlineError.setText(message);
                    inlineError.setVisibility(View.VISIBLE);
                    allOrders.clear();
                    refreshPurchaseUi();
                }
            });

            return view;
        }

        private void setupFilterTabs() {
            filterTabsContainer.removeAllViews();
            Map<String, Integer> counts = buildStatusCounts(allOrders);

            for (Map.Entry<String, String> entry : PURCHASE_TABS.entrySet()) {
                String tabKey = entry.getKey();
                String label = entry.getValue();
                boolean active = tabKey.equals(selectedTab);
                int count = counts.getOrDefault(tabKey, 0);
                filterTabsContainer.addView(createTabView(tabKey, label, count, active));
            }
        }

        private View createTabView(String tabKey, String label, int count, boolean active) {
            LinearLayout tab = new LinearLayout(requireContext());
            tab.setOrientation(LinearLayout.HORIZONTAL);
            tab.setGravity(Gravity.CENTER_VERTICAL);
            int padH = dpToPx(12);
            int padV = dpToPx(9);
            tab.setPadding(padH, padV, padH, padV);
            tab.setBackgroundResource(active ? R.drawable.bg_purchase_tab_active : R.drawable.bg_purchase_tab_inactive);

            TextView labelView = new TextView(requireContext());
            labelView.setText(label);
            labelView.setTextSize(TypedValue.COMPLEX_UNIT_SP, 13f);
            labelView.setTypeface(null, active ? Typeface.BOLD : Typeface.NORMAL);
            labelView.setTextColor(active ? 0xFF166534 : 0xFF374151);
            tab.addView(labelView);

            if (count > 0) {
                TextView badge = new TextView(requireContext());
                badge.setText(String.valueOf(count));
                badge.setTextSize(TypedValue.COMPLEX_UNIT_SP, 11f);
                badge.setTextColor(0xFFFFFFFF);
                badge.setTypeface(null, Typeface.BOLD);
                badge.setBackgroundResource(R.drawable.bg_purchase_tab_badge);
                badge.setGravity(Gravity.CENTER);
                int badgePad = dpToPx(4);
                badge.setPadding(badgePad, badgePad / 2, badgePad, badgePad / 2);
                LinearLayout.LayoutParams badgeParams = new LinearLayout.LayoutParams(
                    ViewGroup.LayoutParams.WRAP_CONTENT,
                    ViewGroup.LayoutParams.WRAP_CONTENT
                );
                badgeParams.setMarginStart(dpToPx(6));
                tab.addView(badge, badgeParams);
            }

            LinearLayout.LayoutParams tabParams = new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.WRAP_CONTENT,
                ViewGroup.LayoutParams.WRAP_CONTENT
            );
            tabParams.setMarginEnd(dpToPx(8));
            tab.setLayoutParams(tabParams);
            tab.setOnClickListener(v -> {
                selectedTab = tabKey;
                refreshPurchaseUi();
            });
            return tab;
        }

        private void refreshPurchaseUi() {
            setupFilterTabs();
            renderOrders(filterOrders(allOrders, selectedTab));
        }

        private List<OrderInfo> filterOrders(List<OrderInfo> orders, String tabKey) {
            List<OrderInfo> filtered = new ArrayList<>();
            for (OrderInfo order : orders) {
                if (matchesTab(order, tabKey)) {
                    filtered.add(order);
                }
            }
            return filtered;
        }

        private boolean matchesTab(OrderInfo order, String tabKey) {
            if ("all".equals(tabKey)) {
                return true;
            }
            String status = order.deliveryStatus == null ? "to_pay" : order.deliveryStatus.toLowerCase(Locale.US);
            if ("to_ship".equals(tabKey)) {
                return Arrays.asList("to_ship", "ready_for_pickup", "accepted_by_rider").contains(status);
            }
            if ("to_receive".equals(tabKey)) {
                return Arrays.asList("delivered_to_rider", "to_receive", "delivered").contains(status);
            }
            if ("to_review".equals(tabKey)) {
                return "completed".equals(status);
            }
            if ("return_refund".equals(tabKey)) {
                return Arrays.asList(
                    "return_requested", "return_approved", "return_picked_up", "return_refund"
                ).contains(status);
            }
            return tabKey.equals(status);
        }

        private Map<String, Integer> buildStatusCounts(List<OrderInfo> orders) {
            Map<String, Integer> counts = new LinkedHashMap<>();
            for (String tabKey : PURCHASE_TABS.keySet()) {
                counts.put(tabKey, 0);
            }
            for (OrderInfo order : orders) {
                counts.put("all", counts.get("all") + 1);
                String bucket = countBucket(order.deliveryStatus);
                if (counts.containsKey(bucket)) {
                    counts.put(bucket, counts.get(bucket) + 1);
                }
                if ("completed".equalsIgnoreCase(order.deliveryStatus)) {
                    counts.put("to_review", counts.get("to_review") + 1);
                }
            }
            return counts;
        }

        private String countBucket(String deliveryStatus) {
            String status = deliveryStatus == null ? "to_pay" : deliveryStatus.toLowerCase(Locale.US);
            if (Arrays.asList("ready_for_pickup", "accepted_by_rider").contains(status)) {
                return "to_ship";
            }
            if (Arrays.asList("delivered_to_rider", "delivered", "to_receive").contains(status)) {
                return "to_receive";
            }
            if (Arrays.asList("return_requested", "return_approved", "return_picked_up").contains(status)) {
                return "return_refund";
            }
            if (PURCHASE_TABS.containsKey(status)) {
                return status;
            }
            return "to_pay";
        }

        private void renderOrders(List<OrderInfo> orders) {
            ordersContainer.removeAllViews();
            if (orders.isEmpty()) {
                TextView empty = new TextView(requireContext());
                empty.setText(allOrders.isEmpty() ? "No purchases yet." : "No orders in this category.");
                empty.setTextSize(14f);
                empty.setTextColor(0xFF6B7280);
                empty.setPadding(dpToPx(8), dpToPx(20), dpToPx(8), dpToPx(20));
                ordersContainer.addView(empty);
                return;
            }

            for (OrderInfo order : orders) {
                View row = layoutInflater.inflate(R.layout.item_purchase_order, ordersContainer, false);
                ((TextView) row.findViewById(R.id.purchase_reference)).setText(order.referenceNumber);
                ((TextView) row.findViewById(R.id.purchase_date)).setText(order.orderDate);
                ((TextView) row.findViewById(R.id.purchase_items_summary)).setText(order.itemsSummary);
                ((TextView) row.findViewById(R.id.purchase_total)).setText(String.format(Locale.US, "₱%.2f", order.totalAmount));

                TextView status = row.findViewById(R.id.purchase_status);
                status.setText(getDeliveryStatusLabel(order.deliveryStatus).toUpperCase(Locale.US));
                applyStatusBadgeStyle(status, order.deliveryStatus);

                TextView shipping = row.findViewById(R.id.purchase_shipping_address);
                if (order.shippingAddress == null || order.shippingAddress.trim().isEmpty()) {
                    shipping.setText("Shipping Address: Not available");
                } else {
                    shipping.setText("Shipping Address: " + order.shippingAddress);
                }

                row.findViewById(R.id.btn_message_seller).setOnClickListener(v -> {
                    ((MainActivity) requireActivity()).loadFragment(
                        MessagesFragment.newInstance(order.referenceNumber, order.itemsSummary, true)
                    );
                    Toast.makeText(requireContext(), "Opening seller messages...", Toast.LENGTH_SHORT).show();
                });

                row.findViewById(R.id.btn_order_details).setOnClickListener(v ->
                    openOrderDetails(order)
                );

                Button confirmDeliveryBtn = row.findViewById(R.id.btn_confirm_delivery);
                boolean canConfirmDelivery = order.canConfirmReceived
                    || "delivered".equalsIgnoreCase(order.deliveryStatus);
                confirmDeliveryBtn.setVisibility(canConfirmDelivery ? View.VISIBLE : View.GONE);
                confirmDeliveryBtn.setOnClickListener(v -> {
                    MainActivity host = (MainActivity) requireActivity();
                    new AlertDialog.Builder(requireContext())
                        .setTitle("Confirm Delivery")
                        .setMessage("Confirm that you received order " + order.referenceNumber + "?")
                        .setNegativeButton("Cancel", null)
                        .setPositiveButton("Confirm", (dialog, which) ->
                            host.performCustomerOrderAction(order.orderId, "confirm", new SimpleCallback() {
                                @Override
                                public void onSuccess(String message) {
                                    Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                                    reloadOrdersFromServer();
                                }

                                @Override
                                public void onError(String message) {
                                    Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show();
                                }
                            })
                        )
                        .show();
                });

                LinearLayout postDeliveryActions = row.findViewById(R.id.purchase_post_delivery_actions);
                Button reviewButton = row.findViewById(R.id.btn_review_order);
                Button refundButton = row.findViewById(R.id.btn_return_refund);
                LinearLayout refundInfo = row.findViewById(R.id.purchase_refund_info);
                TextView refundStatus = row.findViewById(R.id.purchase_refund_status);
                TextView refundTo = row.findViewById(R.id.purchase_refund_to);
                TextView returnCode = row.findViewById(R.id.purchase_return_code);
                ImageView returnQr = row.findViewById(R.id.purchase_return_qr);
                boolean successfulDelivery = isSuccessfulDelivery(order.deliveryStatus);

                postDeliveryActions.setVisibility(successfulDelivery ? View.VISIBLE : View.GONE);
                refundInfo.setVisibility(order.refundRequested ? View.VISIBLE : View.GONE);

                if (order.refundRequested) {
                    refundStatus.setText("Return request submitted. Show QR to rider during pickup.");
                    String methodLabel = order.refundMethod == null ? "" : order.refundMethod.toUpperCase(Locale.US);
                    refundTo.setText("Refund to: " + methodLabel + " - " + order.refundAccount);
                    String qrData = order.qrPayload != null && !order.qrPayload.isEmpty()
                        ? order.qrPayload
                        : order.returnCode;
                    returnCode.setText("Return QR: " + (order.returnCode.isEmpty() ? "Ready" : order.returnCode));
                    Bitmap qrBitmap = createQrBitmap(qrData, 220);
                    if (qrBitmap != null) {
                        returnQr.setImageBitmap(qrBitmap);
                    }
                }

                applyPurchaseActionStyle(reviewButton, !order.reviewSubmitted, "outline");
                reviewButton.setText(order.reviewSubmitted ? "Reviewed" : "Review");
                reviewButton.setOnClickListener(v -> showReviewDialog(order));

                boolean canRequest = order.canRequestReturn && !order.refundRequested;
                applyPurchaseActionStyle(refundButton, canRequest, "secondary");
                refundButton.setText(order.refundRequested ? "Refund Requested" : "Return/Refund");
                refundButton.setEnabled(canRequest || order.refundRequested);
                refundButton.setOnClickListener(v -> {
                    if (order.refundRequested) {
                        Toast.makeText(requireContext(), "Return/refund already submitted for this order.", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    if (!order.canRequestReturn) {
                        Toast.makeText(requireContext(), "Return/refund is not available for this order.", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    showReturnRefundDialog(order);
                });

                ordersContainer.addView(row);
            }
        }

        private void openOrderDetails(OrderInfo order) {
            ((MainActivity) requireActivity()).loadFragment(OrderDetailsFragment.newInstance(order));
        }

        static void clearActionButtonTint(Button button) {
            if (button != null) {
                button.setBackgroundTintList(null);
            }
        }

        static void applyPurchaseActionStyle(Button button, boolean enabled, String variant) {
            if (button == null) {
                return;
            }
            button.setAllCaps(false);
            clearActionButtonTint(button);
            if (!enabled) {
                button.setBackgroundResource(R.drawable.bg_purchase_action_muted);
                button.setTextColor(0xFF6B7280);
                button.setEnabled(false);
                return;
            }
            button.setEnabled(true);
            if ("primary".equals(variant)) {
                button.setBackgroundResource(R.drawable.bg_purchase_action_primary);
                button.setTextColor(0xFFFFFFFF);
            } else if ("outline".equals(variant)) {
                button.setBackgroundResource(R.drawable.bg_purchase_action_outline);
                button.setTextColor(0xFF166534);
            } else {
                button.setBackgroundResource(R.drawable.bg_purchase_action_secondary);
                button.setTextColor(0xFF374151);
            }
        }

        private void reloadOrdersFromServer() {
            MainActivity activity = (MainActivity) requireActivity();
            activity.fetchOrdersFromServer(new OrdersCallback() {
                @Override
                public void onSuccess(List<OrderInfo> orders) {
                    if (inlineError != null) {
                        inlineError.setVisibility(View.GONE);
                    }
                    allOrders.clear();
                    allOrders.addAll(orders);
                    refreshPurchaseUi();
                }

                @Override
                public void onError(String message) {
                    if (inlineError != null) {
                        inlineError.setText(message);
                        inlineError.setVisibility(View.VISIBLE);
                    }
                }
            });
        }

        private void applyStatusBadgeStyle(TextView statusView, String status) {
            String key = status == null ? "" : status.toLowerCase(Locale.US);
            if ("cancelled".equals(key) || "failed_delivery".equals(key) || "return_refund".equals(key)) {
                statusView.setBackgroundResource(R.drawable.bg_order_status_danger);
                statusView.setTextColor(0xFFB91C1C);
                return;
            }
            if ("completed".equals(key) || "delivered".equals(key)) {
                statusView.setBackgroundResource(R.drawable.bg_order_status_success);
                statusView.setTextColor(0xFF166534);
                return;
            }
            statusView.setBackgroundResource(R.drawable.bg_order_status_pending);
            statusView.setTextColor(0xFF92400E);
        }

        private boolean isSuccessfulDelivery(String status) {
            if (status == null) {
                return false;
            }
            String key = status.toLowerCase(Locale.US);
            return "delivered".equals(key) || "completed".equals(key);
        }

        private void showReturnRefundDialog(OrderInfo order) {
            View dialogView = layoutInflater.inflate(R.layout.dialog_return_refund_request, null);
            TextView orderReference = dialogView.findViewById(R.id.refund_order_reference);
            EditText reasonInput = dialogView.findViewById(R.id.refund_reason_input);
            Button chooseFilesButton = dialogView.findViewById(R.id.refund_choose_files);
            TextView filesStatus = dialogView.findViewById(R.id.refund_files_status);
            Spinner methodSpinner = dialogView.findViewById(R.id.refund_method_spinner);
            EditText accountInput = dialogView.findViewById(R.id.refund_account_input);
            EditText accountNameInput = dialogView.findViewById(R.id.refund_account_name_input);
            orderReference.setText("Order: " + (order.referenceNumber == null || order.referenceNumber.isEmpty() ? "N/A" : order.referenceNumber));

            final List<Uri> attachmentUris = new ArrayList<>();
            chooseFilesButton.setOnClickListener(v ->
                ((MainActivity) requireActivity()).openRefundAttachmentPicker(uris -> {
                    attachmentUris.clear();
                    if (uris != null) {
                        for (Uri uri : uris) {
                            if (attachmentUris.size() >= 3) {
                                break;
                            }
                            attachmentUris.add(uri);
                        }
                    }
                    if (attachmentUris.isEmpty()) {
                        filesStatus.setText("No file chosen");
                        return;
                    }
                    String firstFile = resolveAttachmentName(attachmentUris.get(0));
                    if (attachmentUris.size() == 1) {
                        filesStatus.setText(firstFile);
                    } else {
                        filesStatus.setText(firstFile + " +" + (attachmentUris.size() - 1) + " more");
                    }
                    Toast.makeText(requireContext(), attachmentUris.size() + " file(s) selected", Toast.LENGTH_SHORT).show();
                })
            );

            List<String> methods = Arrays.asList("GCash", "Maya");
            ArrayAdapter<String> methodAdapter = new ArrayAdapter<>(requireContext(), android.R.layout.simple_spinner_item, methods);
            methodAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
            methodSpinner.setAdapter(methodAdapter);

            MainActivity activity = (MainActivity) requireActivity();
            AlertDialog dialog = new AlertDialog.Builder(requireContext())
                .setTitle("Request Return/Refund")
                .setView(dialogView)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Submit Request", null)
                .create();
            dialog.setOnShowListener(d -> {
                Button positive = dialog.getButton(AlertDialog.BUTTON_POSITIVE);
                positive.setOnClickListener(v -> {
                    String reason = reasonInput.getText() == null ? "" : reasonInput.getText().toString().trim();
                    String account = accountInput.getText() == null ? "" : accountInput.getText().toString().trim();
                    String accountName = accountNameInput.getText() == null ? "" : accountNameInput.getText().toString().trim();
                    if (reason.length() < 10) {
                        Toast.makeText(requireContext(), "Please enter at least 10 characters for reason.", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    if (attachmentUris.isEmpty()) {
                        Toast.makeText(requireContext(), "Please attach at least 1 evidence file.", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    if (account.isEmpty()) {
                        Toast.makeText(requireContext(), "Please provide GCash/Maya number.", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    if (accountName.isEmpty()) {
                        Toast.makeText(requireContext(), "Please provide account name.", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    String methodLabel = String.valueOf(methodSpinner.getSelectedItem());
                    String payoutMethod = methodLabel.toLowerCase(Locale.US).contains("maya") ? "maya" : "gcash";
                    positive.setEnabled(false);
                    positive.setText("Submitting...");
                    activity.submitReturnRefundToServer(
                        order.orderId,
                        order.referenceNumber,
                        reason,
                        payoutMethod,
                        account,
                        accountName,
                        attachmentUris,
                        new SimpleCallback() {
                            @Override
                            public void onSuccess(String message) {
                                order.refundRequested = true;
                                order.deliveryStatus = "return_requested";
                                order.refundReason = reason;
                                order.refundMethod = payoutMethod;
                                order.refundAccount = account + " (" + accountName + ")";
                                Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                                activity.fetchOrdersFromServer(new OrdersCallback() {
                                    @Override
                                    public void onSuccess(List<OrderInfo> orders) {
                                        allOrders.clear();
                                        allOrders.addAll(orders);
                                        refreshPurchaseUi();
                                    }

                                    @Override
                                    public void onError(String msg) {
                                        refreshPurchaseUi();
                                    }
                                });
                                dialog.dismiss();
                            }

                            @Override
                            public void onError(String message) {
                                positive.setEnabled(true);
                                positive.setText("Submit Request");
                                Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show();
                            }
                        }
                    );
                });
            });
            dialog.show();
        }

        private String resolveAttachmentName(Uri uri) {
            if (uri == null) {
                return "Attachment";
            }
            Cursor cursor = requireContext().getContentResolver().query(uri, null, null, null, null);
            if (cursor == null) {
                String last = uri.getLastPathSegment();
                return last == null ? "Attachment" : last;
            }
            try {
                int nameIndex = cursor.getColumnIndex(OpenableColumns.DISPLAY_NAME);
                if (nameIndex >= 0 && cursor.moveToFirst()) {
                    String value = cursor.getString(nameIndex);
                    if (value != null && !value.trim().isEmpty()) {
                        return value;
                    }
                }
                String last = uri.getLastPathSegment();
                return last == null ? "Attachment" : last;
            } finally {
                cursor.close();
            }
        }

        private void showReviewDialog(OrderInfo order) {
            View dialogView = layoutInflater.inflate(R.layout.dialog_submit_review, null);
            TextView reference = dialogView.findViewById(R.id.review_order_reference);
            RatingBar ratingBar = dialogView.findViewById(R.id.review_rating_bar);
            EditText commentInput = dialogView.findViewById(R.id.review_comment_input);
            reference.setText("Order: " + (order.referenceNumber == null ? "N/A" : order.referenceNumber));

            MainActivity activity = (MainActivity) requireActivity();
            AlertDialog dialog = new AlertDialog.Builder(requireContext())
                .setTitle("Submit Review")
                .setView(dialogView)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Submit", null)
                .create();
            dialog.setOnShowListener(d -> {
                Button positive = dialog.getButton(AlertDialog.BUTTON_POSITIVE);
                positive.setOnClickListener(v -> {
                    int rating = Math.max(1, Math.round(ratingBar.getRating()));
                    String comment = commentInput.getText() == null ? "" : commentInput.getText().toString().trim();
                    if (comment.length() < 3) {
                        Toast.makeText(requireContext(), "Please write at least 3 characters.", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    positive.setEnabled(false);
                    positive.setText("Submitting...");
                    activity.submitReviewToServer(order.referenceNumber, order.itemsSummary, rating, comment, new SimpleCallback() {
                        @Override
                        public void onSuccess(String message) {
                            order.reviewSubmitted = true;
                            Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                            refreshPurchaseUi();
                            dialog.dismiss();
                        }

                        @Override
                        public void onError(String message) {
                            positive.setEnabled(true);
                            positive.setText("Submit");
                            Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show();
                        }
                    });
                });
            });
            dialog.show();
        }

        private String generateReturnCode(String reference) {
            long stamp = System.currentTimeMillis() % 100000;
            String base = reference == null ? "ORD" : reference.replaceAll("[^A-Za-z0-9]", "");
            if (base.length() > 8) {
                base = base.substring(base.length() - 8);
            }
            return "RET-" + base.toUpperCase(Locale.US) + "-" + stamp;
        }

        private Bitmap createQrBitmap(String content, int sizePx) {
            try {
                BitMatrix matrix = new MultiFormatWriter().encode(content, BarcodeFormat.QR_CODE, sizePx, sizePx);
                Bitmap bitmap = Bitmap.createBitmap(sizePx, sizePx, Bitmap.Config.RGB_565);
                for (int x = 0; x < sizePx; x++) {
                    for (int y = 0; y < sizePx; y++) {
                        bitmap.setPixel(x, y, matrix.get(x, y) ? 0xFF000000 : 0xFFFFFFFF);
                    }
                }
                return bitmap;
            } catch (Exception e) {
                return null;
            }
        }

        static String getDeliveryStatusLabel(String status) {
            if (status == null || status.trim().isEmpty()) {
                return "To Pay";
            }
            switch (status.toLowerCase(Locale.US)) {
                case "to_pay":
                    return "To Pay";
                case "to_ship":
                    return "Order Placed";
                case "ready_for_pickup":
                    return "Rider Assigned";
                case "accepted_by_rider":
                    return "Accepted by Rider";
                case "delivered_to_rider":
                    return "Picked Up";
                case "to_receive":
                    return "Out for Delivery";
                case "delivered":
                    return "Delivered (Confirm)";
                case "completed":
                    return "Completed";
                case "to_review":
                    return "To Review";
                case "cancelled":
                    return "Cancelled";
                case "return_refund":
                    return "Return/Refund";
                case "failed_delivery":
                    return "Failed Delivery";
                case "return_requested":
                    return "Return Requested";
                case "return_approved":
                    return "Return Approved";
                case "return_picked_up":
                    return "Return Picked Up";
                default:
                    return status.replace('_', ' ');
            }
        }

        private int dpToPx(int dp) {
            return Math.round(dp * requireContext().getResources().getDisplayMetrics().density);
        }
    }

    public static class OrderDetailsFragment extends Fragment {
        private static final String ARG_REFERENCE = "reference";
        private static final String ARG_DATE = "date";
        private static final String ARG_STATUS = "status";
        private static final String ARG_ITEMS = "items";
        private static final String ARG_TOTAL = "total";
        private static final String ARG_ADDRESS = "address";
        private static final String ARG_CONTACT = "contact";
        private static final String ARG_REVIEW_SUBMITTED = "review_submitted";
        private static final String ARG_REFUND_REQUESTED = "refund_requested";
        private static final String ARG_REFUND_REASON = "refund_reason";
        private static final String ARG_REFUND_METHOD = "refund_method";
        private static final String ARG_REFUND_ACCOUNT = "refund_account";
        private static final String ARG_RETURN_CODE = "return_code";
        private static final String ARG_ORDER_ID = "order_id";
        private static final String ARG_CAN_PAY = "can_pay";
        private static final String ARG_CAN_CANCEL = "can_cancel";
        private static final String ARG_CAN_CONFIRM = "can_confirm";

        private View detailsRoot;
        private TextView odStatusView;
        private LinearLayout deliveryActions;
        private LinearLayout postDeliveryActions;
        private View liveTrackingCard;
        private Button payBtn;
        private Button cancelBtn;
        private Button confirmBtn;
        private TextView trackingStatusText;
        private int orderId;
        private String currentStatus = "to_pay";
        private boolean canPay;
        private boolean canCancel;
        private boolean canConfirm;

        static OrderDetailsFragment newInstance(OrderInfo order) {
            OrderDetailsFragment fragment = new OrderDetailsFragment();
            Bundle args = new Bundle();
            args.putInt(ARG_ORDER_ID, order.orderId);
            args.putBoolean(ARG_CAN_PAY, order.canPay);
            args.putBoolean(ARG_CAN_CANCEL, order.canCancel);
            args.putBoolean(ARG_CAN_CONFIRM, order.canConfirmReceived);
            args.putString(ARG_REFERENCE, order.referenceNumber);
            args.putString(ARG_DATE, order.orderDate);
            args.putString(ARG_STATUS, order.deliveryStatus);
            args.putString(ARG_ITEMS, order.itemsSummary);
            args.putDouble(ARG_TOTAL, order.totalAmount);
            args.putString(ARG_ADDRESS, order.shippingAddress);
            args.putString(ARG_CONTACT, order.shippingContact);
            args.putBoolean(ARG_REVIEW_SUBMITTED, order.reviewSubmitted);
            args.putBoolean(ARG_REFUND_REQUESTED, order.refundRequested);
            args.putString(ARG_REFUND_REASON, order.refundReason);
            args.putString(ARG_REFUND_METHOD, order.refundMethod);
            args.putString(ARG_REFUND_ACCOUNT, order.refundAccount);
            args.putString(ARG_RETURN_CODE, order.qrPayload != null && !order.qrPayload.isEmpty()
                ? order.qrPayload
                : order.returnCode);
            fragment.setArguments(args);
            return fragment;
        }

        @Override
        public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_order_details, container, false);
            MainActivity activity = (MainActivity) requireActivity();

            Bundle args = getArguments();
            String reference = args != null ? args.getString(ARG_REFERENCE, "N/A") : "N/A";
            String date = args != null ? args.getString(ARG_DATE, "N/A") : "N/A";
            String status = args != null ? args.getString(ARG_STATUS, "to_pay") : "to_pay";
            String items = args != null ? args.getString(ARG_ITEMS, "No items") : "No items";
            double total = args != null ? args.getDouble(ARG_TOTAL, 0) : 0;
            String address = args != null ? args.getString(ARG_ADDRESS, "") : "";
            String contact = args != null ? args.getString(ARG_CONTACT, "") : "";
            boolean reviewSubmitted = args != null && args.getBoolean(ARG_REVIEW_SUBMITTED, false);
            boolean refundRequested = args != null && args.getBoolean(ARG_REFUND_REQUESTED, false);
            String refundReason = args != null ? args.getString(ARG_REFUND_REASON, "") : "";
            String refundMethod = args != null ? args.getString(ARG_REFUND_METHOD, "GCash") : "GCash";
            String refundAccount = args != null ? args.getString(ARG_REFUND_ACCOUNT, "") : "";
            String returnCode = args != null ? args.getString(ARG_RETURN_CODE, "") : "";
            orderId = args != null ? args.getInt(ARG_ORDER_ID, 0) : 0;
            canPay = args != null && args.getBoolean(ARG_CAN_PAY, false);
            canCancel = args != null && args.getBoolean(ARG_CAN_CANCEL, false);
            canConfirm = args != null && args.getBoolean(ARG_CAN_CONFIRM, false);
            currentStatus = status;
            if (!canConfirm && "delivered".equalsIgnoreCase(currentStatus)) {
                canConfirm = true;
            }

            detailsRoot = view;
            ((TextView) view.findViewById(R.id.od_reference)).setText(reference);
            ((TextView) view.findViewById(R.id.od_date)).setText(date);
            ((TextView) view.findViewById(R.id.od_total)).setText(String.format(Locale.US, "₱%.2f", total));
            ((TextView) view.findViewById(R.id.od_items_summary)).setText(items);
            ((TextView) view.findViewById(R.id.od_subtotal)).setText(String.format(Locale.US, "Subtotal: ₱%.2f", total));
            ((TextView) view.findViewById(R.id.od_shipping_fee)).setText("Shipping: ₱0.00");
            ((TextView) view.findViewById(R.id.od_grand_total)).setText(String.format(Locale.US, "Total: ₱%.2f", total));

            odStatusView = view.findViewById(R.id.od_status);
            odStatusView.setText(MyPurchaseFragment.getDeliveryStatusLabel(status).toUpperCase(Locale.US));
            applyStatusBadgeStyle(odStatusView, status);

            TextView addressView = view.findViewById(R.id.od_delivery_address);
            if (address == null || address.trim().isEmpty()) {
                addressView.setText("Shipping Address: Not available");
            } else {
                addressView.setText("Shipping Address: " + address);
            }

            TextView contactView = view.findViewById(R.id.od_delivery_contact);
            if (contact == null || contact.trim().isEmpty()) {
                contactView.setText("Contact Number: +63 900 000 0000");
            } else {
                contactView.setText("Contact Number: " + contact);
            }

            applyTimelineState(view, status);

            view.findViewById(R.id.btn_back_from_order_details).setOnClickListener(v ->
                activity.loadFragment(new MyPurchaseFragment()));

            view.findViewById(R.id.btn_message_seller_details).setOnClickListener(v -> {
                activity.loadFragment(MessagesFragment.newInstance(reference, items, true));
                Toast.makeText(requireContext(), "Opening seller messages...", Toast.LENGTH_SHORT).show();
            });

            deliveryActions = view.findViewById(R.id.od_delivery_actions);
            payBtn = view.findViewById(R.id.btn_pay_order);
            cancelBtn = view.findViewById(R.id.btn_cancel_order);
            confirmBtn = view.findViewById(R.id.btn_confirm_received);
            trackingStatusText = view.findViewById(R.id.od_tracking_status_text);
            TextView trackingMeta = view.findViewById(R.id.od_tracking_meta);
            WebView trackingMapWebView = view.findViewById(R.id.od_tracking_map_webview);
            liveTrackingCard = view.findViewById(R.id.od_live_tracking_card);
            postDeliveryActions = view.findViewById(R.id.od_post_delivery_actions);

            MyPurchaseFragment.clearActionButtonTint(payBtn);
            MyPurchaseFragment.clearActionButtonTint(cancelBtn);
            MyPurchaseFragment.clearActionButtonTint(confirmBtn);
            MyPurchaseFragment.clearActionButtonTint(view.findViewById(R.id.btn_track_order));
            MyPurchaseFragment.clearActionButtonTint(view.findViewById(R.id.btn_message_seller_details));

            payBtn.setOnClickListener(v -> runOrderAction(activity, orderId, "pay", odStatusView, trackingStatusText));
            cancelBtn.setOnClickListener(v -> runOrderAction(activity, orderId, "cancel", odStatusView, trackingStatusText));
            confirmBtn.setOnClickListener(v -> {
                new AlertDialog.Builder(requireContext())
                    .setTitle("Confirm Delivery")
                    .setMessage("Confirm that you received this order?")
                    .setNegativeButton("Cancel", null)
                    .setPositiveButton("Confirm", (dialog, which) ->
                        runOrderAction(activity, orderId, "confirm", odStatusView, trackingStatusText)
                    )
                    .show();
            });
            view.findViewById(R.id.btn_track_order).setOnClickListener(v -> activity.refreshLiveTrackingNow());

            refreshDeliveryUi(currentStatus, canPay, canCancel, canConfirm);

            if (orderId > 0 && MainActivity.shouldShowLiveTracking(currentStatus)) {
                liveTrackingCard.setVisibility(View.VISIBLE);
                activity.startLiveTracking(orderId, trackingMapWebView, trackingStatusText, trackingMeta);
            } else {
                liveTrackingCard.setVisibility(View.GONE);
            }

            Button reviewButton = view.findViewById(R.id.btn_review_order_details);
            MyPurchaseFragment.applyPurchaseActionStyle(reviewButton, !reviewSubmitted, "outline");
            reviewButton.setText(reviewSubmitted ? "Reviewed" : "Review Order");
            reviewButton.setOnClickListener(v ->
                showReviewDialog(reference, items, reviewButton)
            );
            Button returnRefundButton = view.findViewById(R.id.btn_return_refund_details);
            MyPurchaseFragment.applyPurchaseActionStyle(returnRefundButton, !refundRequested, "secondary");
            returnRefundButton.setOnClickListener(v ->
                Toast.makeText(requireContext(), "Return/refund request already submitted in Purchase tab.", Toast.LENGTH_SHORT).show()
            );

            androidx.cardview.widget.CardView refundCard = view.findViewById(R.id.od_refund_card);
            if (refundRequested && returnCode != null && !returnCode.isEmpty()) {
                refundCard.setVisibility(View.VISIBLE);
                ((TextView) view.findViewById(R.id.od_refund_reason)).setText(
                    "Reason: " + (refundReason == null || refundReason.isEmpty() ? "Not specified" : refundReason)
                );
                ((TextView) view.findViewById(R.id.od_refund_to)).setText(
                    "Refund to: " + refundMethod + " - " + refundAccount
                );
                ((TextView) view.findViewById(R.id.od_return_code)).setText("Return QR - " + returnCode);
                ImageView refundQr = view.findViewById(R.id.od_return_qr);
                Bitmap qrBitmap = createQrBitmap(returnCode, 280);
                if (qrBitmap != null) {
                    refundQr.setImageBitmap(qrBitmap);
                }
            } else {
                refundCard.setVisibility(View.GONE);
            }

            return view;
        }

        @Override
        public void onResume() {
            super.onResume();
            MainActivity activity = (MainActivity) getActivity();
            if (activity == null || orderId <= 0) {
                return;
            }
            activity.setOrderDetailsUiUpdater(this::refreshDeliveryUi);
            activity.refreshOrderDetailsFromServer(orderId);
        }

        @Override
        public void onPause() {
            MainActivity activity = (MainActivity) getActivity();
            if (activity != null) {
                activity.clearOrderDetailsUiUpdater();
            }
            super.onPause();
        }

        @Override
        public void onDestroyView() {
            MainActivity activity = (MainActivity) getActivity();
            if (activity != null) {
                activity.clearOrderDetailsUiUpdater();
                activity.stopLiveTracking();
            }
            super.onDestroyView();
        }

        private void refreshDeliveryUi(
            String deliveryStatus,
            boolean showPay,
            boolean showCancel,
            boolean showConfirm
        ) {
            if (deliveryActions == null || odStatusView == null || detailsRoot == null) {
                return;
            }
            currentStatus = deliveryStatus == null ? currentStatus : deliveryStatus;
            canPay = showPay;
            canCancel = showCancel;
            canConfirm = showConfirm || "delivered".equalsIgnoreCase(currentStatus);

            odStatusView.setText(MyPurchaseFragment.getDeliveryStatusLabel(currentStatus).toUpperCase(Locale.US));
            applyStatusBadgeStyle(odStatusView, currentStatus);
            applyTimelineState(detailsRoot, currentStatus);

            boolean showDeliveryActions = canPay || canCancel || canConfirm
                || Arrays.asList("delivered_to_rider", "to_receive", "delivered", "completed")
                    .contains(currentStatus.toLowerCase(Locale.US));
            deliveryActions.setVisibility(showDeliveryActions ? View.VISIBLE : View.GONE);
            payBtn.setVisibility(canPay ? View.VISIBLE : View.GONE);
            cancelBtn.setVisibility(canCancel ? View.VISIBLE : View.GONE);
            confirmBtn.setVisibility(canConfirm ? View.VISIBLE : View.GONE);

            boolean successfulDelivery = isSuccessfulDelivery(currentStatus);
            if (postDeliveryActions != null) {
                postDeliveryActions.setVisibility(successfulDelivery ? View.VISIBLE : View.GONE);
            }

            MainActivity activity = (MainActivity) getActivity();
            if (activity != null && liveTrackingCard != null) {
                if (orderId > 0 && MainActivity.shouldShowLiveTracking(currentStatus)) {
                    liveTrackingCard.setVisibility(View.VISIBLE);
                } else if (!canConfirm) {
                    liveTrackingCard.setVisibility(View.GONE);
                }
            }

            if ("delivered".equalsIgnoreCase(currentStatus) && trackingStatusText != null) {
                trackingStatusText.setText("Rider marked this order as delivered. Please confirm receipt.");
            }
        }

        private void runOrderAction(MainActivity activity, int orderId, String action, TextView statusView, TextView trackingStatus) {
            if (orderId <= 0) {
                Toast.makeText(requireContext(), "Invalid order.", Toast.LENGTH_SHORT).show();
                return;
            }
            activity.performCustomerOrderAction(orderId, action, new SimpleCallback() {
                @Override
                public void onSuccess(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                    activity.loadFragment(new MyPurchaseFragment());
                }

                @Override
                public void onError(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show();
                }
            });
        }

        private void applyTimelineState(View root, String status) {
            int step = getTrackingStep(status);
            int[] dotIds = {
                R.id.od_step_dot_1, R.id.od_step_dot_2, R.id.od_step_dot_3, R.id.od_step_dot_4, R.id.od_step_dot_5
            };
            int[] labelIds = {
                R.id.od_step_label_1, R.id.od_step_label_2, R.id.od_step_label_3, R.id.od_step_label_4, R.id.od_step_label_5
            };

            for (int i = 0; i < dotIds.length; i++) {
                boolean active = i < step;
                View dot = root.findViewById(dotIds[i]);
                TextView label = root.findViewById(labelIds[i]);
                dot.setBackgroundResource(active ? R.drawable.bg_timeline_dot_active : R.drawable.bg_timeline_dot_inactive);
                label.setTextColor(active ? 0xFF166534 : 0xFF6B7280);
                label.setTypeface(null, active ? Typeface.BOLD : Typeface.NORMAL);
            }
        }

        private int getTrackingStep(String status) {
            if (status == null) {
                return 1;
            }
            switch (status.toLowerCase(Locale.US)) {
                case "to_ship":
                    return 1;
                case "ready_for_pickup":
                case "accepted_by_rider":
                    return 2;
                case "delivered_to_rider":
                    return 3;
                case "to_receive":
                    return 4;
                case "delivered":
                case "completed":
                    return 5;
                default:
                    return 1;
            }
        }

        private void applyStatusBadgeStyle(TextView statusView, String status) {
            String key = status == null ? "" : status.toLowerCase(Locale.US);
            if ("cancelled".equals(key) || "failed_delivery".equals(key) || "return_refund".equals(key)) {
                statusView.setBackgroundResource(R.drawable.bg_order_status_danger);
                statusView.setTextColor(0xFFB91C1C);
                return;
            }
            if ("completed".equals(key) || "delivered".equals(key)) {
                statusView.setBackgroundResource(R.drawable.bg_order_status_success);
                statusView.setTextColor(0xFF166534);
                return;
            }
            statusView.setBackgroundResource(R.drawable.bg_order_status_pending);
            statusView.setTextColor(0xFF92400E);
        }

        private boolean isSuccessfulDelivery(String status) {
            if (status == null) {
                return false;
            }
            String key = status.toLowerCase(Locale.US);
            return "delivered".equals(key) || "completed".equals(key);
        }

        private Bitmap createQrBitmap(String content, int sizePx) {
            try {
                BitMatrix matrix = new MultiFormatWriter().encode(content, BarcodeFormat.QR_CODE, sizePx, sizePx);
                Bitmap bitmap = Bitmap.createBitmap(sizePx, sizePx, Bitmap.Config.RGB_565);
                for (int x = 0; x < sizePx; x++) {
                    for (int y = 0; y < sizePx; y++) {
                        bitmap.setPixel(x, y, matrix.get(x, y) ? 0xFF000000 : 0xFFFFFFFF);
                    }
                }
                return bitmap;
            } catch (Exception e) {
                return null;
            }
        }

        private void showReviewDialog(String reference, String items, Button reviewButton) {
            View dialogView = LayoutInflater.from(requireContext()).inflate(R.layout.dialog_submit_review, null);
            TextView refView = dialogView.findViewById(R.id.review_order_reference);
            RatingBar ratingBar = dialogView.findViewById(R.id.review_rating_bar);
            EditText commentInput = dialogView.findViewById(R.id.review_comment_input);
            refView.setText("Order: " + (reference == null ? "N/A" : reference));

            MainActivity activity = (MainActivity) requireActivity();
            AlertDialog dialog = new AlertDialog.Builder(requireContext())
                .setTitle("Submit Review")
                .setView(dialogView)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Submit", null)
                .create();
            dialog.setOnShowListener(d -> {
                Button submit = dialog.getButton(AlertDialog.BUTTON_POSITIVE);
                submit.setOnClickListener(v -> {
                    int rating = Math.max(1, Math.round(ratingBar.getRating()));
                    String comment = commentInput.getText() == null ? "" : commentInput.getText().toString().trim();
                    if (comment.length() < 3) {
                        Toast.makeText(requireContext(), "Please write at least 3 characters.", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    submit.setEnabled(false);
                    submit.setText("Submitting...");
                    activity.submitReviewToServer(reference, items, rating, comment, new SimpleCallback() {
                        @Override
                        public void onSuccess(String message) {
                            MyPurchaseFragment.applyPurchaseActionStyle(reviewButton, false, "outline");
                            reviewButton.setText("Reviewed");
                            Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                            dialog.dismiss();
                        }

                        @Override
                        public void onError(String message) {
                            submit.setEnabled(true);
                            submit.setText("Submit");
                            Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show();
                        }
                    });
                });
            });
            dialog.show();
        }

    }

    public static class CartFragment extends Fragment {
        @Override
        public View onCreateView(LayoutInflater inflater, android.view.ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_cart, container, false);
            MainActivity activity = (MainActivity) requireActivity();
            LinearLayout cartContainer = view.findViewById(R.id.cart_items_container);
            TextView itemCount = view.findViewById(R.id.cart_item_count);
            TextView totalAmount = view.findViewById(R.id.cart_total_amount);
            TextView emptyState = view.findViewById(R.id.cart_empty_state);
            Button checkoutButton = view.findViewById(R.id.btn_checkout);
            activity.loadCartFromServer(new CartLoadCallback() {
                @Override
                public void onSuccess() {
                    renderCartItems(activity, inflater, cartContainer, itemCount, totalAmount, emptyState);
                }

                @Override
                public void onError(String message) {
                    renderCartItems(activity, inflater, cartContainer, itemCount, totalAmount, emptyState);
                    Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                }
            });

            checkoutButton.setOnClickListener(v -> {
                if (!activity.isUserLoggedIn()) {
                    Toast.makeText(requireContext(), "Please login before checkout", Toast.LENGTH_SHORT).show();
                    activity.loadFragment(new LoginFragment());
                    return;
                }
                if (activity.getCartItemCount() == 0) {
                    Toast.makeText(requireContext(), "Your cart is empty", Toast.LENGTH_SHORT).show();
                    return;
                }
                activity.showCheckoutDialog(new SimpleCallback() {
                    @Override
                    public void onSuccess(String message) {
                        Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                        activity.clearCart();
                        renderCartItems(activity, inflater, cartContainer, itemCount, totalAmount, emptyState);
                    }

                    @Override
                    public void onError(String message) {
                        Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                    }
                });
            });
            return view;
        }

        private void renderCartItems(
            MainActivity activity,
            LayoutInflater inflater,
            LinearLayout cartContainer,
            TextView itemCount,
            TextView totalAmount,
            TextView emptyState
        ) {
            cartContainer.removeAllViews();

            List<CartItem> items = activity.getCartItems();
            if (items.isEmpty()) {
                emptyState.setVisibility(View.VISIBLE);
            } else {
                emptyState.setVisibility(View.GONE);
            }

            for (CartItem item : items) {
                View row = inflater.inflate(R.layout.item_cart_product, cartContainer, false);
                ImageView image = row.findViewById(R.id.cart_item_image);
                TextView name = row.findViewById(R.id.cart_item_name);
                TextView details = row.findViewById(R.id.cart_item_details);
                TextView price = row.findViewById(R.id.cart_item_price);
                TextView quantity = row.findViewById(R.id.cart_item_quantity);

                image.setImageResource(item.imageResId);
                name.setText(item.name);
                details.setText(item.details);
                price.setText(String.format(Locale.US, "₱%.2f", item.price));
                quantity.setText(String.valueOf(item.quantity));

                row.findViewById(R.id.btn_cart_minus).setOnClickListener(v -> {
                    activity.updateCartQuantity(item.cartKey, -1);
                    renderCartItems(activity, inflater, cartContainer, itemCount, totalAmount, emptyState);
                });
                row.findViewById(R.id.btn_cart_plus).setOnClickListener(v -> {
                    activity.updateCartQuantity(item.cartKey, 1);
                    renderCartItems(activity, inflater, cartContainer, itemCount, totalAmount, emptyState);
                });

                cartContainer.addView(row);
            }

            itemCount.setText(activity.getCartItemCount() + " item(s)");
            totalAmount.setText(String.format(Locale.US, "₱%.2f", activity.getCartTotal()));
        }

    }

    public static class SettingsFragment extends Fragment {
        @Override
        public View onCreateView(LayoutInflater inflater, android.view.ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_settings, container, false);
            MainActivity activity = (MainActivity) requireActivity();
            TextView profileName = view.findViewById(R.id.settings_profile_name);
            TextView profileEmail = view.findViewById(R.id.settings_profile_email);
            View logoutAction = view.findViewById(R.id.action_logout);

            String registeredEmail = activity.getRegisteredEmail();
            if (activity.isUserLoggedIn() && !registeredEmail.isEmpty()) {
                profileName.setText(activity.getRegisteredFullName());
                profileEmail.setText(registeredEmail);
                logoutAction.setVisibility(View.VISIBLE);
            } else {
                profileName.setText("Guest User");
                profileEmail.setText("Login to manage your account");
                logoutAction.setVisibility(View.GONE);
            }

            view.findViewById(R.id.action_edit_profile).setOnClickListener(v -> {
                if (!activity.isUserLoggedIn()) {
                    Toast.makeText(requireContext(), "Please login first", Toast.LENGTH_SHORT).show();
                    activity.loadFragment(new LoginFragment());
                    return;
                }
                showEditProfileDialog(activity, profileName, profileEmail);
            });
            view.findViewById(R.id.action_change_password).setOnClickListener(v -> {
                if (!activity.isUserLoggedIn()) {
                    Toast.makeText(requireContext(), "Please login first", Toast.LENGTH_SHORT).show();
                    activity.loadFragment(new LoginFragment());
                    return;
                }
                showChangePasswordDialog(activity);
            });
            view.findViewById(R.id.action_logout).setOnClickListener(v ->
                activity.onLogout());
            return view;
        }

        private void showEditProfileDialog(MainActivity activity, TextView profileName, TextView profileEmail) {
            LinearLayout form = new LinearLayout(requireContext());
            form.setOrientation(LinearLayout.VERTICAL);
            int padding = dpToPx(16);
            form.setPadding(padding, padding / 2, padding, padding / 2);

            EditText nameInput = new EditText(requireContext());
            nameInput.setHint("Full Name");
            nameInput.setText(activity.getRegisteredFullName());
            form.addView(nameInput);

            EditText emailInput = new EditText(requireContext());
            emailInput.setHint("Email");
            emailInput.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_EMAIL_ADDRESS);
            emailInput.setText(activity.getRegisteredEmail());
            form.addView(emailInput);

            AlertDialog dialog = new AlertDialog.Builder(requireContext())
                .setTitle("Edit Profile")
                .setView(form)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Save", null)
                .create();

            dialog.setOnShowListener(d -> dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(v -> {
                String fullName = nameInput.getText().toString().trim();
                String email = emailInput.getText().toString().trim();
                if (fullName.isEmpty() || email.isEmpty()) {
                    Toast.makeText(requireContext(), "Name and email are required", Toast.LENGTH_SHORT).show();
                    return;
                }
                if (!Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
                    Toast.makeText(requireContext(), "Enter a valid email", Toast.LENGTH_SHORT).show();
                    return;
                }
                activity.updateProfileWithServer(fullName, email, new SimpleCallback() {
                    @Override
                    public void onSuccess(String message) {
                        profileName.setText(fullName);
                        profileEmail.setText(email);
                        Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                        dialog.dismiss();
                    }

                    @Override
                    public void onError(String message) {
                        Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                    }
                });
            }));

            dialog.show();
        }

        private void showChangePasswordDialog(MainActivity activity) {
            LinearLayout form = new LinearLayout(requireContext());
            form.setOrientation(LinearLayout.VERTICAL);
            int padding = dpToPx(16);
            form.setPadding(padding, padding / 2, padding, padding / 2);

            EditText currentPassword = new EditText(requireContext());
            currentPassword.setHint("Current Password");
            currentPassword.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);
            form.addView(currentPassword);

            EditText newPassword = new EditText(requireContext());
            newPassword.setHint("New Password");
            newPassword.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);
            form.addView(newPassword);

            EditText confirmPassword = new EditText(requireContext());
            confirmPassword.setHint("Confirm New Password");
            confirmPassword.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_VARIATION_PASSWORD);
            form.addView(confirmPassword);

            AlertDialog dialog = new AlertDialog.Builder(requireContext())
                .setTitle("Change Password")
                .setView(form)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Update", null)
                .create();

            dialog.setOnShowListener(d -> dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(v -> {
                String currentValue = currentPassword.getText().toString().trim();
                String newValue = newPassword.getText().toString().trim();
                String confirmValue = confirmPassword.getText().toString().trim();

                if (currentValue.isEmpty() || newValue.isEmpty() || confirmValue.isEmpty()) {
                    Toast.makeText(requireContext(), "Complete all password fields", Toast.LENGTH_SHORT).show();
                    return;
                }
                if (newValue.length() < 8) {
                    Toast.makeText(requireContext(), "New password must be at least 8 characters", Toast.LENGTH_SHORT).show();
                    return;
                }
                if (!newValue.equals(confirmValue)) {
                    Toast.makeText(requireContext(), "New passwords do not match", Toast.LENGTH_SHORT).show();
                    return;
                }
                activity.updatePasswordWithServer(currentValue, newValue, new SimpleCallback() {
                    @Override
                    public void onSuccess(String message) {
                        Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                        dialog.dismiss();
                    }

                    @Override
                    public void onError(String message) {
                        Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                    }
                });
            }));

            dialog.show();
        }

        private int dpToPx(int dp) {
            float density = requireContext().getResources().getDisplayMetrics().density;
            return Math.round(dp * density);
        }
    }

    public static class AdminOrdersFragment extends Fragment {
        private final List<JSONObject> riders = new ArrayList<>();

        @Override
        public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_role_dashboard, container, false);
            MainActivity activity = (MainActivity) requireActivity();
            ((TextView) view.findViewById(R.id.role_dashboard_title)).setText("Admin — Orders");
            ((TextView) view.findViewById(R.id.role_dashboard_subtitle)).setText("Assign riders and update delivery status");
            LinearLayout containerLayout = view.findViewById(R.id.role_orders_container);
            view.findViewById(R.id.btn_role_logout).setOnClickListener(v -> activity.onLogout());
            view.findViewById(R.id.btn_role_refresh).setOnClickListener(v -> loadOrders(activity, containerLayout));
            loadRiders(activity);
            loadOrders(activity, containerLayout);
            return view;
        }

        private void loadRiders(MainActivity activity) {
            activity.fetchRidersList(new SimpleCallback() {
                @Override
                public void onSuccess(String responseBody) {
                    riders.clear();
                    try {
                        JSONObject root = new JSONObject(responseBody);
                        JSONObject data = root.optJSONObject("data");
                        JSONArray arr = data != null ? data.optJSONArray("riders") : null;
                        if (arr != null) {
                            for (int i = 0; i < arr.length(); i++) {
                                JSONObject r = arr.optJSONObject(i);
                                if (r != null) {
                                    riders.add(r);
                                }
                            }
                        }
                    } catch (Exception ignored) {
                    }
                }

                @Override
                public void onError(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                }
            });
        }

        private void loadOrders(MainActivity activity, LinearLayout containerLayout) {
            activity.fetchAdminOrders(new SimpleCallback() {
                @Override
                public void onSuccess(String responseBody) {
                    containerLayout.removeAllViews();
                    try {
                        JSONObject root = new JSONObject(responseBody);
                        JSONObject data = root.optJSONObject("data");
                        JSONArray orders = data != null ? data.optJSONArray("orders") : null;
                        if (orders == null || orders.length() == 0) {
                            TextView empty = new TextView(requireContext());
                            empty.setText("No orders found.");
                            containerLayout.addView(empty);
                            return;
                        }
                        for (int i = 0; i < orders.length(); i++) {
                            JSONObject order = orders.optJSONObject(i);
                            if (order != null) {
                                containerLayout.addView(buildOrderRow(activity, order));
                            }
                        }
                    } catch (Exception e) {
                        Toast.makeText(requireContext(), "Invalid response", Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onError(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show();
                }
            });
        }

        private View buildOrderRow(MainActivity activity, JSONObject order) {
            View row = getLayoutInflater().inflate(R.layout.item_role_order, null);
            int orderId = order.optInt("order_id", 0);
            String status = order.optString("delivery_status", "");
            ((TextView) row.findViewById(R.id.role_order_reference)).setText(order.optString("reference_number", "#" + orderId));
            ((TextView) row.findViewById(R.id.role_order_status)).setText(MyPurchaseFragment.getDeliveryStatusLabel(status));
            ((TextView) row.findViewById(R.id.role_order_customer)).setText(
                "Customer: " + order.optString("customer_name", "") + " | Payment: " + order.optString("payment_status", "")
            );
            JSONObject shipment = order.optJSONObject("shipment");
            String addr = shipment != null ? shipment.optString("shipping_address", "") : "";
            ((TextView) row.findViewById(R.id.role_order_address)).setText(addr.isEmpty() ? "No address" : addr);
            LinearLayout actions = row.findViewById(R.id.role_order_actions);
            if ("to_pay".equals(status)) {
                addActionButton(actions, "Mark To Ship", () ->
                    activity.adminUpdateDeliveryStatus(orderId, "to_ship", simpleRefresh(activity)));
            }
            addActionButton(actions, "Assign Rider", () -> showAssignRiderDialog(activity, orderId, actions));
            if ("to_ship".equals(status) || "ready_for_pickup".equals(status)) {
                addActionButton(actions, "Ready for Pickup", () ->
                    activity.adminUpdateDeliveryStatus(orderId, "ready_for_pickup", simpleRefresh(activity)));
            }
            if ("delivered".equals(status)) {
                addActionButton(actions, "Confirm Completed", () ->
                    activity.adminUpdateDeliveryStatus(orderId, "completed", simpleRefresh(activity)));
            }
            return row;
        }

        private void addActionButton(LinearLayout parent, String label, Runnable action) {
            Button btn = new Button(requireContext());
            btn.setText(label);
            btn.setAllCaps(false);
            LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
            lp.topMargin = 6;
            btn.setLayoutParams(lp);
            btn.setOnClickListener(v -> action.run());
            parent.addView(btn);
        }

        private SimpleCallback simpleRefresh(MainActivity activity) {
            return new SimpleCallback() {
                @Override
                public void onSuccess(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                    activity.loadFragment(new AdminOrdersFragment());
                }

                @Override
                public void onError(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show();
                }
            };
        }

        private void showAssignRiderDialog(MainActivity activity, int orderId, LinearLayout ignored) {
            if (riders.isEmpty()) {
                Toast.makeText(requireContext(), "No riders available.", Toast.LENGTH_SHORT).show();
                return;
            }
            String[] names = new String[riders.size()];
            for (int i = 0; i < riders.size(); i++) {
                names[i] = riders.get(i).optString("name", "Rider");
            }
            new AlertDialog.Builder(requireContext())
                .setTitle("Select rider")
                .setItems(names, (d, which) -> {
                    int riderId = riders.get(which).optInt("id", 0);
                    activity.adminAssignRider(orderId, riderId, simpleRefresh(activity));
                })
                .show();
        }
    }

    public static class RiderDeliveriesFragment extends Fragment {
        @Override
        public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_role_dashboard, container, false);
            MainActivity activity = (MainActivity) requireActivity();
            ((TextView) view.findViewById(R.id.role_dashboard_title)).setText("Rider — Deliveries");
            ((TextView) view.findViewById(R.id.role_dashboard_subtitle)).setText("Accept, pickup, deliver, and return pickups");
            LinearLayout containerLayout = view.findViewById(R.id.role_orders_container);
            view.findViewById(R.id.btn_role_logout).setOnClickListener(v -> activity.onLogout());
            view.findViewById(R.id.btn_role_refresh).setOnClickListener(v -> loadDeliveries(activity, containerLayout));
            loadDeliveries(activity, containerLayout);
            return view;
        }

        private void loadDeliveries(MainActivity activity, LinearLayout containerLayout) {
            activity.fetchRiderOrders("active", new SimpleCallback() {
                @Override
                public void onSuccess(String responseBody) {
                    containerLayout.removeAllViews();
                    try {
                        JSONObject root = new JSONObject(responseBody);
                        JSONArray orders = root.optJSONObject("data") != null
                            ? root.optJSONObject("data").optJSONArray("orders") : null;
                        if (orders == null || orders.length() == 0) {
                            TextView empty = new TextView(requireContext());
                            empty.setText("No assigned deliveries.");
                            containerLayout.addView(empty);
                            return;
                        }
                        for (int i = 0; i < orders.length(); i++) {
                            JSONObject order = orders.optJSONObject(i);
                            if (order != null) {
                                containerLayout.addView(buildRiderRow(activity, order));
                            }
                        }
                    } catch (Exception e) {
                        Toast.makeText(requireContext(), "Invalid response", Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onError(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show();
                }
            });
        }

        private View buildRiderRow(MainActivity activity, JSONObject order) {
            View row = getLayoutInflater().inflate(R.layout.item_role_order, null);
            int orderId = order.optInt("order_id", 0);
            String status = order.optString("delivery_status", "");
            ((TextView) row.findViewById(R.id.role_order_reference)).setText(order.optString("reference_number", ""));
            ((TextView) row.findViewById(R.id.role_order_status)).setText(MyPurchaseFragment.getDeliveryStatusLabel(status));
            ((TextView) row.findViewById(R.id.role_order_customer)).setText(
                "Customer: " + order.optString("customer_name", "")
            );
            JSONObject shipment = order.optJSONObject("shipment");
            String addr = shipment != null ? shipment.optString("shipping_address", "") : "";
            ((TextView) row.findViewById(R.id.role_order_address)).setText(addr);
            LinearLayout actions = row.findViewById(R.id.role_order_actions);
            SimpleCallback refresh = new SimpleCallback() {
                @Override
                public void onSuccess(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                    activity.loadFragment(new RiderDeliveriesFragment());
                }

                @Override
                public void onError(String message) {
                    Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show();
                }
            };
            if ("ready_for_pickup".equals(status)) {
                addRiderBtn(actions, "Accept Delivery", () ->
                    activity.riderUpdateStatus(orderId, "accepted_by_rider", null, refresh));
            }
            if ("accepted_by_rider".equals(status)) {
                addRiderBtn(actions, "Picked Up from Store", () ->
                    activity.riderUpdateStatus(orderId, "delivered_to_rider", null, refresh));
            }
            if ("delivered_to_rider".equals(status) || "failed_delivery".equals(status)) {
                addRiderBtn(actions, "Start Delivery", () ->
                    activity.riderUpdateStatus(orderId, "to_receive", null, refresh));
            }
            if ("to_receive".equals(status)) {
                addRiderBtn(actions, "Submit Delivery Proof", () -> activity.openDeliveryProofPicker(orderId));
                addRiderBtn(actions, "Reschedule", () -> {
                    Map<String, String> extra = new HashMap<>();
                    extra.put("reschedule_at", new java.text.SimpleDateFormat("yyyy-MM-dd", Locale.US).format(new java.util.Date()));
                    extra.put("reschedule_reason", "Rescheduled from mobile app");
                    activity.riderUpdateStatus(orderId, "reschedule_delivery", extra, refresh);
                });
                addRiderBtn(actions, "Failed Delivery", () -> {
                    Map<String, String> extra = new HashMap<>();
                    extra.put("cancel_reason", "Unable to deliver");
                    activity.riderUpdateStatus(orderId, "failed_delivery", extra, refresh);
                });
            }
            if ("return_approved".equals(status)) {
                addRiderBtn(actions, "Accept Return Pickup", () ->
                    activity.riderUpdateStatus(orderId, "accept_return_pickup", null, refresh));
                addRiderBtn(actions, "Scan Return QR", () -> promptReturnQr(activity, orderId, refresh));
            }
            return row;
        }

        private void addRiderBtn(LinearLayout parent, String label, Runnable action) {
            Button btn = new Button(requireContext());
            btn.setText(label);
            btn.setAllCaps(false);
            LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
            lp.topMargin = 6;
            btn.setLayoutParams(lp);
            btn.setOnClickListener(v -> action.run());
            parent.addView(btn);
        }

        private void promptReturnQr(MainActivity activity, int orderId, SimpleCallback refresh) {
            EditText input = new EditText(requireContext());
            input.setHint("Paste or scan QR payload");
            new AlertDialog.Builder(requireContext())
                .setTitle("Return QR")
                .setView(input)
                .setPositiveButton("Submit", (d, w) -> {
                    Map<String, String> extra = new HashMap<>();
                    extra.put("return_qr_scan", input.getText().toString().trim());
                    activity.riderUpdateStatus(orderId, "return_picked_up", extra, refresh);
                })
                .setNegativeButton("Cancel", null)
                .show();
        }
    }

    public static class ProductDetailFragment extends Fragment {
        private ProductCatalogEntry currentProduct;

        @Override
        public View onCreateView(LayoutInflater inflater, android.view.ViewGroup container, Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_product_detail, container, false);
            MainActivity activity = (MainActivity) requireActivity();
            Bundle args = getArguments();
            int productId = args != null ? args.getInt("productId", 0) : 0;
            int imageResId = args != null ? args.getInt("productImageRes", R.drawable.black_elite_v2) : R.drawable.black_elite_v2;

            view.findViewById(R.id.product_image).setVisibility(View.VISIBLE);
            ((ImageView) view.findViewById(R.id.product_image)).setImageResource(imageResId);

            view.findViewById(R.id.back_button).setOnClickListener(v ->
                activity.loadFragment(new HomeFragment()));

            view.findViewById(R.id.btn_add_to_cart).setOnClickListener(v -> {
                if (currentProduct == null) {
                    Toast.makeText(requireContext(), "Product still loading", Toast.LENGTH_SHORT).show();
                    return;
                }
                activity.showAddToCartFlow(this, currentProduct);
            });

            ProductCatalogEntry cached = activity.getProductById(productId);
            if (cached != null) {
                bindProduct(view, cached, imageResId);
            } else if (productId > 0) {
                activity.fetchProductDetailFromServer(productId, new ProductDetailCallback() {
                    @Override
                    public void onSuccess(ProductCatalogEntry product) {
                        bindProduct(view, product, imageResId);
                    }

                    @Override
                    public void onError(String message) {
                        Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                    }
                });
            }

            return view;
        }

        private void bindProduct(View view, ProductCatalogEntry product, int imageResId) {
            currentProduct = product;
            TextView productName = view.findViewById(R.id.product_name);
            TextView productPrice = view.findViewById(R.id.product_price);
            TextView productCategory = view.findViewById(R.id.product_category);
            TextView productSpecs = view.findViewById(R.id.product_specs);
            TextView productRating = view.findViewById(R.id.product_rating);
            TextView productStock = view.findViewById(R.id.product_stock);
            TextView productFlavors = view.findViewById(R.id.product_flavors);
            ImageView productImage = view.findViewById(R.id.product_image);

            productImage.setImageResource(imageResId);
            productName.setText(product.name);
            productPrice.setText(String.format(Locale.US, "₱%.2f", product.price));
            productCategory.setText(product.category);
            if (product.puffs > 0) {
                productSpecs.setText(String.format(Locale.US, "%,d Puffs", product.puffs));
                productSpecs.setVisibility(View.VISIBLE);
            } else {
                productSpecs.setVisibility(View.GONE);
            }
            productRating.setText(product.formatRatingLabel());
            productStock.setText(product.formatStockLabel());

            if (product.needsFlavorSelection()) {
                productFlavors.setText(product.formatFlavorSummary());
                view.findViewById(R.id.flavor_card).setVisibility(View.VISIBLE);
            } else {
                view.findViewById(R.id.flavor_card).setVisibility(View.GONE);
            }
        }
    }
}
