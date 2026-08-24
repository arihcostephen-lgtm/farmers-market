# Local Farm Market – Full-Stack E-Commerce Marketplace for Local Farmers

**Live Demo** → [https://farmersmarket.infinityfreeapp.com/projects/Local_Farm_Market/]  
**GitHub Repository** → [github.com/Stephen-Ben/Local_Farm_Market](https://github.com/arihcostephen-lgtm/Local_Farm_Market)

![Untitled design (3)](https://github.com/user-attachments/assets/29a0e280-93e0-447f-84a8-560db79b7cac)



> A production-ready, secure, and scalable online marketplace that connects local farmers directly with customers. Built from scratch using raw PHP & MySQL – no frameworks – proving deep understanding of core web technologies.

---

## Why This Project Stands Out (For Recruiters & Clients)

- Clean, maintainable code structure with proper separation of concerns
- Complete user role system: Customer → Seller → Admin  
- Real payment gateway integration (SSLCommerz – supports sandbox & live)  
- Admin approval workflow for seller products (prevents fake listings)  
- Location-based delivery logic  
- Secure authentication, prepared statements, password hashing, file validation  
- Responsive UI with Bootstrap 5 + custom CSS  
- Fully functional cart, checkout, order tracking & review system  
- Deployed on real shared hosting (cPanel) – not just localhost  

**This is not a tutorial project – it's a real-world deployable application.**

---

## Instant Demo Access (No signup needed)

| Role            | Direct Link                                                                 | Credentials                  |
|-----------------|-----------------------------------------------------------------------------|------------------------------|
| Public Website  | [Open Site](https://github.com/arihcostephenlgtm/farmers-market/)              | –                            |
| Customer        | [Customer Login](https://github.com/aihcostephen-lgtm/projects/farmers-market/login.php) | `user@gmail.com` / `12345`   |
| Seller Dashboard| [Seller Panel](https://github.com/arihcostephen-lgtm.com/farmers-market/seller.php)  | `seller@gmail.com` / `12345` |
| Admin Panel     | [Admin Panel](https://github.com/arihcostephen-lgtm/farmers-market/Local_Farm_Market/admin/)       | `stephenarichco@gmail.com` / `12345`  |

> Feel free to place orders, add products (as seller), approve/reject listings (as admin) – everything works end-to-end.

---

## Core Features

| Feature                        | Description                                                                 |
|--------------------------------|-----------------------------------------------------------------------------|
| Category-wise Product Browsing | Dairy, Pure Meat, Fresh Milk, Sweets, Whole-Cow Deals, etc.                |
| Shopping Cart & Checkout       | Session-based cart with real-time total calculation                        |
| SSLCommerz Payment Gateway     | Sandbox + Live mode ready (BD's most popular gateway)                      |
| Location-based Delivery        | Delivery area determined from user's address                               |
| Seller Product Approval Flow   | All listings stay `pending` → Admin verifies authenticity → `published`   |
| Multi-role Dashboard           | Separate panels for Customer, Seller & Super Admin                        |
| Order Tracking & Reviews       | Customers can track orders and leave ratings/comments                      |
| Blog Management                | Admin can create and publish articles                                      |
| Secure File Uploads            | Only allowed image types, renamed files, size limits                       |

---

## Technology Stack

| Layer          | Technology                                          |
|----------------|-----------------------------------------------------|
| Backend        | PHP 8+                |
| Database       | MySQL (optimized schema with proper relationships) |
| Frontend       | HTML5, CSS3, Bootstrap 5, Vanilla JS                |
| Payment        | SSLCommerz (Bangladesh) – Sandbox + Live            |
| Security       | `sha1()`, prepared statements, input sanitization, CSRF-ready structure |
| Deployment     | cPanel Shared Hosting (real production environment) |

---

## Security Best Practices Implemented

- Passwords hashed with `PASSWORD_DEFAULT`
- All queries use prepared statements (MySQLi)
- Input validation + sanitization (client + server side)
- Restricted file uploads (type, size, renaming)
- Session management with regeneration
- HTTPS enforced in production

## Email Notifications

The application sends support, product, inquiry, and other notifications through PHPMailer. Configure SMTP before using email features by setting these environment variables for the Apache/PHP process:

```text
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notifications@example.com
MAIL_FROM_NAME=Farmers Market
```

On Windows/XAMPP, define the variables in the Apache service environment or `httpd.conf`, then restart Apache. Use an application password for providers such as Gmail. The sender address falls back to the configured site contact email when `MAIL_FROM_ADDRESS` is omitted. Email failures are logged through PHP's error log and do not expose SMTP credentials to users.

## Uganda Mobile Money Provisioning

Orders use UGX mobile money through the provider APIs. The payment callback must be reachable over HTTPS by the provider. Add these variables to the Apache/PHP environment before enabling live payments:

```text
PAYMENT_CALLBACK_URL=https://your-domain.example/payment_callback.php
PAYMENT_CALLBACK_SECRET=generate-a-long-random-value
MTN_UGANDA_BASE_URL=https://sandbox.momodeveloper.mtn.com
MTN_UGANDA_TARGET_ENVIRONMENT=sandbox
MTN_UGANDA_SUBSCRIPTION_KEY=...
MTN_UGANDA_API_USER=...
MTN_UGANDA_API_KEY=...
AIRTEL_UGANDA_BASE_URL=https://openapiuat.airtel.africa
AIRTEL_UGANDA_CLIENT_ID=...
AIRTEL_UGANDA_CLIENT_SECRET=...
USSD_SHORT_CODE=*165#
SMS_API_URL=https://api.africastalking.com/version1/messaging
SMS_USERNAME=...
SMS_API_KEY=...
```

Create an MTN MoMo Uganda Collection application and subscription key, then provision the API user and key in the MTN developer portal. Create an Airtel Money Uganda merchant application and use its UAT credentials first. Replace the MTN base URL and target environment with the values supplied for production only after UAT reconciliation succeeds. `USSD_SHORT_CODE` is a manual fallback: USSD is not a generic push-payment API, so its merchant code and customer instructions must match the merchant account issued by MTN or Airtel. SMS uses Africa's Talking as the default adapter; an empty SMS configuration safely disables receipts without blocking payment confirmation.

The callback records provider responses idempotently, marks the order paid only for a successful status, and sends an SMS receipt when configured. Include the same secret in the callback URL as `https://your-domain.example/payment_callback.php?secret=generate-a-long-random-value` when registering it with the provider. Protect the callback at the web-server layer with HTTPS and the provider's callback/signature mechanism where the approved account supports one; do not expose API secrets in the repository.

---

## Project Structure (Clean & Organized)
/
├── assets/ # CSS, JS, images <br>
├── uploads/ # Product & seller images<br>
├── admin/ # Full admin panel<br>
├── seller/ # Seller dashboard<br>
├── includes/<br>
│ ├── config.php # Core configuration<br>
│ ├── db.php # Database connection<br>
│ └── functions.php # Reusable helper functions<br>
├── index.php # Homepage<br>
├── login.php<br>
├── register.php<br>
├── cart.php<br>
├── checkout.php<br>
└── farm_market.sql # Database dump<br>


---

## ⚙️ Quick Local Setup

1. **Clone the repository**
2. **Import `farm_market.sql`** into your MySQL server
3. Update `includes/config.php` with your database credentials
4. Add **SSLCommerz Sandbox API keys** (instructions included in comments)
5. Make the following folders writable:
   - `uploads/`
   - `seller_photos/`
6. Run the project in browser:  
   👉 http://localhost/Local_Farm_Market/

---

## 🗄️ Database Overview (Key Tables)

| Table | Purpose |
|-------|---------|
| **users** | Role-based authentication (admin/seller/customer) |
| **sellers** | Farmer profile info |
| **categories** | Product categories |
| **products** | Full product details with pending/published status |
| **orders** | Customer order data |
| **order_items** | Items within each order |
| **comments** | Product reviews & ratings |
| **blogs** | Blog management for SEO & content |

---

## 👨‍💻 Developer / Owner

**Shohanur Rahman Shohan**  
Full-Stack Software Engineer  
Available for Remote, Freelance & Full-time roles

- 🌐 Portfolio:
- 📧 Email: **stephenarichco@gmail.com**  
- 🔗 LinkedIn: https://linkedin.com/in/Stephen_Arihco 

---


## License

[![License: MIT](https://img.shields.io/badge/License-MIT-brightgreen.svg?style=flat-square)](https://opensource.org/licenses/MIT)      
