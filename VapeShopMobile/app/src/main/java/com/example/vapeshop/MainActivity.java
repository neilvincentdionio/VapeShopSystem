package com.example.vapeshop;

import android.os.Bundle;
import android.graphics.Bitmap;
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
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;
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
    private final LinkedHashMap<String, CartItem> cartItems = new LinkedHashMap<>();
    private final Map<Integer, ProductCatalogEntry> productCatalogById = new HashMap<>();
    private final Map<String, Integer> productIdByName = new HashMap<>();
    private final ExecutorService networkExecutor = Executors.newSingleThreadExecutor();
    private final Handler mainHandler = new Handler(Looper.getMainLooper());
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
        String referenceNumber;
        String orderDate;
        String deliveryStatus;
        String itemsSummary;
        double totalAmount;
        String shippingAddress;
        String shippingContact;
        boolean reviewSubmitted;
        boolean refundRequested;
        String refundReason;
        String refundMethod;
        String refundAccount;
        String returnCode;

        OrderInfo(
            String referenceNumber,
            String orderDate,
            String deliveryStatus,
            String itemsSummary,
            double totalAmount,
            String shippingAddress,
            String shippingContact
        ) {
            this.referenceNumber = referenceNumber;
            this.orderDate = orderDate;
            this.deliveryStatus = deliveryStatus;
            this.itemsSummary = itemsSummary;
            this.totalAmount = totalAmount;
            this.shippingAddress = shippingAddress;
            this.shippingContact = shippingContact;
            this.reviewSubmitted = false;
            this.refundRequested = false;
            this.refundReason = "";
            this.refundMethod = "GCash";
            this.refundAccount = "";
            this.returnCode = "";
        }
    }

    private static class SupportMessage {
        String senderName;
        String messageBody;
        String createdAt;
        boolean fromCustomer;

        SupportMessage(String senderName, String messageBody, String createdAt, boolean fromCustomer) {
            this.senderName = senderName;
            this.messageBody = messageBody;
            this.createdAt = createdAt;
            this.fromCustomer = fromCustomer;
        }
    }

    private static class ChatOrderOption {
        String reference;
        String productSummary;

        ChatOrderOption(String reference, String productSummary) {
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
        loadFragment(new LoginFragment());
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        networkExecutor.shutdownNow();
    }

    private void setupNavigation() {
        findViewById(R.id.nav_home).setOnClickListener(v ->
            loadFragment(new HomeFragment()));

        findViewById(R.id.nav_cart).setOnClickListener(v ->
            loadFragment(new CartFragment()));

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
        loadFragment(new HomeFragment());
        Toast.makeText(this, "Login successful", Toast.LENGTH_SHORT).show();
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
                    saveAccountLocally(
                        fullName, savedEmail, password, phone, street, city, barangay, postalCode, province, country, latitude, longitude
                    );
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
                        6.1164,
                        125.1716
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

    public void checkoutOnServer(double totalAmount, SimpleCallback callback) {
        Map<String, String> params = new HashMap<>();
        String shippingAddress = getRegisteredShippingAddress();
        String contactNumber = getRegisteredPhone();
        double latitude = getRegisteredLatitude();
        double longitude = getRegisteredLongitude();
        params.put("email", getRegisteredEmail());
        params.put("total_amount", String.format(Locale.US, "%.2f", totalAmount));
        params.put("shipping_address", shippingAddress);
        params.put("delivery_address", shippingAddress);
        params.put("contact_number", contactNumber);
        params.put("phone", contactNumber);
        params.put("latitude", String.format(Locale.US, "%.6f", latitude));
        params.put("longitude", String.format(Locale.US, "%.6f", longitude));
        params.put("customer_latitude", String.format(Locale.US, "%.6f", latitude));
        params.put("customer_longitude", String.format(Locale.US, "%.6f", longitude));
        params.put("delivery_latitude", String.format(Locale.US, "%.6f", latitude));
        params.put("delivery_longitude", String.format(Locale.US, "%.6f", longitude));
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
                            orders.add(new OrderInfo(
                                order.optString("reference_number", ""),
                                order.optString("order_date", ""),
                                deliveryStatus,
                                summary.length() == 0 ? "No items" : summary.toString(),
                                order.optDouble("total_amount", 0),
                                shippingAddress,
                                shippingContact
                            ));
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
        if (!isUserLoggedIn()) {
            callback.onError("Please login to open messages");
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        String[] endpoints = {
            "support_messages_list.php",
            "support_chat_list.php",
            "messages_list.php",
            "chat_list.php"
        };
        tryFetchMessagesEndpoint(endpoints, 0, params, callback);
    }

    public void sendSupportMessageToServer(String message, String relatedOrderReference, String relatedProduct, SimpleCallback callback) {
        if (!isUserLoggedIn()) {
            callback.onError("Please login to send message");
            return;
        }
        Map<String, String> params = new HashMap<>();
        params.put("email", getRegisteredEmail());
        params.put("message", message);
        params.put("sender_role", "customer");
        params.put("role", "customer");
        params.put("sender", "customer");
        params.put("content", message);
        if (relatedOrderReference != null && !relatedOrderReference.trim().isEmpty()) {
            params.put("related_order", relatedOrderReference);
            params.put("order_reference", relatedOrderReference);
            params.put("reference_number", relatedOrderReference);
        }
        if (relatedProduct != null && !relatedProduct.trim().isEmpty()) {
            params.put("related_product", relatedProduct);
            params.put("product_name", relatedProduct);
            params.put("subject", relatedProduct);
        }
        String[] endpoints = {
            "support_messages_send.php",
            "support_chat_send.php",
            "messages_send.php",
            "chat_send.php"
        };
        trySendMessageEndpoint(endpoints, 0, params, callback);
    }

    public void submitReturnRefundToServer(
        String orderReference,
        String reason,
        String refundMethod,
        String refundAccount,
        String returnCode,
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
        params.put("refund_method", refundMethod);
        params.put("refund_account", refundAccount);
        params.put("return_code", returnCode);
        params.put("order_reference", orderReference == null ? "" : orderReference);
        params.put("reference_number", orderReference == null ? "" : orderReference);
        params.put("related_order", orderReference == null ? "" : orderReference);
        params.put("status", "requested");
        params.put("evidence_count", String.valueOf(attachments == null ? 0 : attachments.size()));
        params.put("evidence_names", buildAttachmentNames(attachments));

        String[] endpoints = {
            "return_refund_request.php",
            "returns_request.php",
            "refund_request.php",
            "returns_submit.php",
            "return_refund_submit.php"
        };
        trySubmitReturnEndpoint(endpoints, 0, params, callback);
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
            String createdAt = item.optString("created_at",
                item.optString("sent_at", item.optString("timestamp", item.optString("date", ""))));
            String role = item.optString("sender_role",
                item.optString("role", item.optString("type", ""))).toLowerCase(Locale.US);
            String senderEmail = item.optString("sender_email", item.optString("email", "")).toLowerCase(Locale.US);
            boolean fromCustomer = role.contains("customer")
                || role.contains("user")
                || senderEmail.equals(customerEmail);
            if (body != null && !body.trim().isEmpty()) {
                messages.add(new SupportMessage(sender, body, createdAt, fromCustomer));
            }
        }
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

    private void onLogout() {
        isLoggedIn = false;
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

            setupAddressMappings();
            bindSpinner(countrySpinner, Arrays.asList("Philippines"));
            bindSpinner(provinceSpinner, Arrays.asList("South Cotabato"));
            setupAddressAutoSelect();
            chooseIdButton.setOnClickListener(v -> idPickerLauncher.launch("image/*"));

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
                MainActivity activity = (MainActivity) requireActivity();
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
            TextView status = view.findViewById(R.id.messages_status);
            LinearLayout threadContainer = view.findViewById(R.id.messages_thread_container);
            EditText input = view.findViewById(R.id.messages_input);
            androidx.core.widget.NestedScrollView scroll = view.findViewById(R.id.messages_scroll);
            Spinner relatedOrderSpinner = view.findViewById(R.id.messages_related_order_spinner);
            final List<ChatOrderOption> relatedOrderOptions = new ArrayList<>();
            relatedOrderOptions.add(new ChatOrderOption("", ""));
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

            loadMessages(activity, threadContainer, scroll, status);
            activity.fetchOrdersFromServer(new OrdersCallback() {
                @Override
                public void onSuccess(List<OrderInfo> orders) {
                    relatedOrderOptions.clear();
                    relatedOrderOptions.add(new ChatOrderOption("", ""));
                    int targetSelection = 0;
                    for (OrderInfo order : orders) {
                        String reference = order.referenceNumber == null ? "" : order.referenceNumber;
                        String productSummary = order.itemsSummary == null ? "" : order.itemsSummary;
                        if (!preselectedOrder.isEmpty() && preselectedOrder.equalsIgnoreCase(reference)) {
                            productSummary = preselectedProduct.isEmpty() ? productSummary : preselectedProduct;
                        }
                        relatedOrderOptions.add(new ChatOrderOption(reference, productSummary));
                        if (!preselectedOrder.isEmpty() && preselectedOrder.equalsIgnoreCase(reference)) {
                            targetSelection = relatedOrderOptions.size() - 1;
                        }
                    }
                    if (!preselectedOrder.isEmpty() && targetSelection == 0) {
                        relatedOrderOptions.add(new ChatOrderOption(preselectedOrder, preselectedProduct));
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

            view.findViewById(R.id.btn_mark_messages_read).setOnClickListener(v -> {
                status.setText("All messages marked as read.");
                Toast.makeText(requireContext(), "Messages updated", Toast.LENGTH_SHORT).show();
            });

            view.findViewById(R.id.btn_send_message).setOnClickListener(v -> {
                String message = input.getText() == null ? "" : input.getText().toString().trim();
                if (message.isEmpty()) {
                    Toast.makeText(requireContext(), "Type a message first", Toast.LENGTH_SHORT).show();
                    return;
                }
                ChatOrderOption selected = (ChatOrderOption) relatedOrderSpinner.getSelectedItem();
                String relatedOrderRef = selected != null ? selected.reference : "";
                String relatedProduct = selected != null ? selected.productSummary : "";
                activity.sendSupportMessageToServer(message, relatedOrderRef, relatedProduct, new SimpleCallback() {
                    @Override
                    public void onSuccess(String response) {
                        input.setText("");
                        status.setText(response);
                        loadMessages(activity, threadContainer, scroll, status);
                    }

                    @Override
                    public void onError(String message) {
                        status.setText(message);
                        Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                    }
                });
            });

            return view;
        }

        private void loadMessages(
            MainActivity activity,
            LinearLayout threadContainer,
            androidx.core.widget.NestedScrollView scroll,
            TextView status
        ) {
            threadContainer.removeAllViews();
            activity.fetchSupportMessagesFromServer(new MessagesCallback() {
                @Override
                public void onSuccess(List<SupportMessage> messages) {
                    if (messages.isEmpty()) {
                        addMessageBubble(
                            threadContainer,
                            "System",
                            "I will connect you with an admin/seller. Please wait for a human reply.",
                            false,
                            0xFFF5EFE6
                        );
                        status.setText("No replies yet. Start the conversation below.");
                    } else {
                        for (SupportMessage item : messages) {
                            String sender = item.senderName == null || item.senderName.trim().isEmpty() ? "Support" : item.senderName;
                            String fullText = item.messageBody;
                            if (item.createdAt != null && !item.createdAt.trim().isEmpty()) {
                                fullText = item.messageBody + "\n" + item.createdAt;
                            }
                            int bubbleColor = item.fromCustomer ? 0xFFE9F9EF : 0xFFF3F4F6;
                            addMessageBubble(threadContainer, sender, fullText, item.fromCustomer, bubbleColor);
                        }
                        status.setText("Connected to support conversation.");
                    }
                    scroll.post(() -> scroll.fullScroll(View.FOCUS_DOWN));
                }

                @Override
                public void onError(String message) {
                    addMessageBubble(
                        threadContainer,
                        "System",
                        "Unable to load server messages.\n" + message,
                        false,
                        0xFFFEE2E2
                    );
                    status.setText(message);
                }
            });
        }

        private void addMessageBubble(LinearLayout parent, String sender, String message, boolean isUser, int bubbleColor) {
            LinearLayout row = new LinearLayout(requireContext());
            row.setOrientation(LinearLayout.HORIZONTAL);
            row.setGravity(isUser ? Gravity.END : Gravity.START);

            TextView bubble = new TextView(requireContext());
            bubble.setText(sender + "\n" + message);
            bubble.setTextColor(0xFF1F2937);
            bubble.setTextSize(TypedValue.COMPLEX_UNIT_SP, 13f);
            bubble.setBackgroundColor(bubbleColor);
            int pad = dpToPx(10);
            bubble.setPadding(pad, pad, pad, pad);

            LinearLayout.LayoutParams bubbleParams = new LinearLayout.LayoutParams(
                dpToPx(230),
                ViewGroup.LayoutParams.WRAP_CONTENT
            );
            bubbleParams.topMargin = dpToPx(6);
            row.addView(bubble, bubbleParams);
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
                    refundTo.setText("Refund to: " + order.refundMethod + " - " + order.refundAccount);
                    returnCode.setText("Return QR: " + order.returnCode);
                    Bitmap qrBitmap = createQrBitmap(order.returnCode, 220);
                    if (qrBitmap != null) {
                        returnQr.setImageBitmap(qrBitmap);
                    }
                }

                reviewButton.setEnabled(!order.reviewSubmitted);
                reviewButton.setText(order.reviewSubmitted ? "Reviewed" : "Review");
                reviewButton.setOnClickListener(v -> showReviewDialog(order));

                refundButton.setEnabled(!order.refundRequested);
                refundButton.setText(order.refundRequested ? "Refund Requested" : "Return/Refund");
                refundButton.setOnClickListener(v -> showReturnRefundDialog(order));

                ordersContainer.addView(row);
            }
        }

        private void openOrderDetails(OrderInfo order) {
            ((MainActivity) requireActivity()).loadFragment(OrderDetailsFragment.newInstance(order));
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

            List<String> methods = Arrays.asList("GCash", "Maya", "Bank Transfer");
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
                    String method = String.valueOf(methodSpinner.getSelectedItem());
                    String returnCode = generateReturnCode(order.referenceNumber);
                    positive.setEnabled(false);
                    positive.setText("Submitting...");
                    activity.submitReturnRefundToServer(
                        order.referenceNumber,
                        reason,
                        method,
                        account + " (" + accountName + ")",
                        returnCode,
                        attachmentUris,
                        new SimpleCallback() {
                            @Override
                            public void onSuccess(String message) {
                                order.refundRequested = true;
                                order.deliveryStatus = "return_requested";
                                order.refundReason = reason;
                                order.refundMethod = method;
                                order.refundAccount = account + " (" + accountName + ")";
                                order.returnCode = returnCode;
                                Toast.makeText(requireContext(), message, Toast.LENGTH_SHORT).show();
                                refreshPurchaseUi();
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

        static OrderDetailsFragment newInstance(OrderInfo order) {
            OrderDetailsFragment fragment = new OrderDetailsFragment();
            Bundle args = new Bundle();
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
            args.putString(ARG_RETURN_CODE, order.returnCode);
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

            ((TextView) view.findViewById(R.id.od_reference)).setText(reference);
            ((TextView) view.findViewById(R.id.od_date)).setText(date);
            ((TextView) view.findViewById(R.id.od_total)).setText(String.format(Locale.US, "₱%.2f", total));
            ((TextView) view.findViewById(R.id.od_items_summary)).setText(items);
            ((TextView) view.findViewById(R.id.od_subtotal)).setText(String.format(Locale.US, "Subtotal: ₱%.2f", total));
            ((TextView) view.findViewById(R.id.od_shipping_fee)).setText("Shipping: ₱0.00");
            ((TextView) view.findViewById(R.id.od_grand_total)).setText(String.format(Locale.US, "Total: ₱%.2f", total));

            TextView statusView = view.findViewById(R.id.od_status);
            statusView.setText(MyPurchaseFragment.getDeliveryStatusLabel(status).toUpperCase(Locale.US));
            applyStatusBadgeStyle(statusView, status);

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

            boolean successfulDelivery = isSuccessfulDelivery(status);
            LinearLayout postDeliveryActions = view.findViewById(R.id.od_post_delivery_actions);
            postDeliveryActions.setVisibility(successfulDelivery ? View.VISIBLE : View.GONE);

            Button reviewButton = view.findViewById(R.id.btn_review_order_details);
            reviewButton.setEnabled(!reviewSubmitted);
            reviewButton.setText(reviewSubmitted ? "Reviewed" : "Review Order");
            reviewButton.setOnClickListener(v ->
                showReviewDialog(reference, items, reviewButton)
            );
            view.findViewById(R.id.btn_return_refund_details).setOnClickListener(v ->
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
                            reviewButton.setEnabled(false);
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
                activity.checkoutOnServer(activity.getCartTotal(), new SimpleCallback() {
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
