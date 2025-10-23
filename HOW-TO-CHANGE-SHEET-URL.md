# 📝 How to Change Your Google Sheets URL

## WordPress Admin Access

1. **Go to WordPress Admin**
   - Log into your WordPress website admin area

2. **Navigate to Location Finder Settings**
   - In the admin sidebar, click **"Location Finder"**
   - This will take you to the main plugin settings page

3. **Update Google Sheets URL**
   - Find the **"Google Sheets Configuration"** section at the top
   - Replace the URL in the **"Google Sheets Export URL"** field
   - Click **"Save Settings"**

## How to Get Your Google Sheets Export URL

1. **Open your Google Sheet**
   - Go to your Google Sheet containing business data

2. **Publish to Web**
   - Click **File** → **Share** → **Publish to web**

3. **Configure Export Settings**
   - Choose **"Comma-separated values (.csv)"** format
   - Select the specific sheet tab if you have multiple tabs
   - Click **"Publish"**

4. **Copy the URL**
   - Copy the generated URL
   - Paste it into the WordPress admin settings

## Required CSV Format

Your Google Sheet must have these columns (can be in any order):

- **name** - Business name
- **category** - Business category (any names work!)
- **suburb** - Location suburb  
- **address** - Full business address
- **instagram** - Instagram handle (without @)

## Examples of Flexible Categories

The system now works with ANY category names:

### Coffee Shops
- "Specialty Coffee", "Chain Coffee", "Local Roasters"

### Restaurants  
- "Fine Dining", "Casual Dining", "Fast Food", "Takeaway"

### Services
- "Legal Services", "Medical Services", "Home Services"

### Retail
- "Clothing", "Electronics", "Home & Garden", "Books"

## Current Default URL

If you haven't changed it yet, the system is using:
```
https://docs.google.com/spreadsheets/d/1A8W-_GwPfCWbkqzyvSRKNC2x6bTzDCwBNS24tNuKCt8/export?format=csv&gid=1952886414
```

## Admin Menu Location

**WordPress Admin → Location Finder → Main Settings Page**

The settings are now at the top of the admin page with a clear form to update your Google Sheets URL!