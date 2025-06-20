# README.md

# Popup Formas de Pagamento

## Description
Popup Formas de Pagamento is a WordPress plugin that displays a popup modal with various payment options for WooCommerce products. Users can view payment methods such as credit card, PIX, and boleto, along with their respective pricing and installment options.

## Features
- Dynamic payment options based on product price.
- Adjustable interest rates for installment payments.
- Discount for PIX payments.
- Settings for boleto payment options.

## Installation
1. Download the plugin files.
2. Upload the `popup-formas-pagamento` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage
To display the payment options modal, use the shortcode:
```
[popup_formas_pagamento]
```
Place this shortcode in any post or page where you want the payment options to appear.

## Configuration
### Admin Settings
To configure the plugin settings:
1. Navigate to the WordPress admin panel.
2. Go to **Settings** > **Popup Formas de Pagamento**.
3. Adjust the interest rates, PIX discount, and boleto payment settings as needed.
4. Save changes to apply the new settings.

## Customization
You can customize the appearance of the modal by editing the `assets/css/style.css` file. For JavaScript functionality, modify the `assets/js/custom.js` file.

## Support
For support, please contact the plugin author at [ANDREI MOTERLE](mailto:andreimoterle@gmail.com).

## Changelog
### Version 2.0
- Initial release with payment options modal and admin settings for interest rates and discounts.