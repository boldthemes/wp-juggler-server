# WP Juggler #

WP Juggler is your ultimate solution for effectively managing an unlimited portfolio of WordPress sites from a single, intuitive dashboard. 

WP Juggler helps you to enhance your operational efficiency, ensure your sites stay current with updates, and reclaim precious time to focus on more impactful projects. 

Being an open-source and completely free tool, WP Juggler is designed to revolutionize your WordPress maintenance experience without costing you a dime.

## The idea behind it ##

WP Juggler is a product of [BoldThemes](https://bold-themes.com/) need to efficiently manage more than [100 demo sites](https://themeforest.net/user/boldthemes/portfolio) showcasing our premium WordPress themes and more than 200 websites resulting from our agency work.

Before the creation of WP Juggler, managing multiple sites was a chaotic, time-consuming process that often resulted in costly maintenance issues. The solutions available in the market were either inadquate, overpriced or fell short in efficient functionality. 

So we have created WP Juggler

## Why choose WP Juggler? ##

There are more than few reasons to consider WP Juggler over existing commercial products.
With WP Juggler you can:

- Track WP Health and WP Debug info for all your WordPress sites
- Seamlessly login into wp-admin's on your sites with one click of a button
- Set uptime monitor for all your sites and receive alerts when site goes down
- Manage, Update, Activate and Deactivate WordPress Plugins
- Host your own plugins fully integrated with WordPress update mechanism
- Track checksums of installed Plugins, Themes and WP Core and receive alerts on all failed checks for additional security
- Track known vulnerabilities of current versions of Plugins and Themes on your sites to avoid being hacked
- Manage and Update themes on your sites
- Track WordPress wp-admin dashboard notices for all your WordPress sites from a cntral point
- Set granular user rights to control who sees what within the network

WP Juggler is fully compatible with WordPress multisite setups. 
It also makes TGMPA plugins fully integreted into WordPress update mechanism. This is important if you have premium themes (such as ones coming from ThemeForest) installed on your sites. Their packaged plugins will be treated the same way as all other plugins in your WordPress installations.

## How does it work - Main concepts ##

WP Juggler has two components:

- WP Juggler Server plugin
- [WP Juggler Client plugin](https://github.com/boldthemes/wp-juggler-client)

WP Juggler Server is a self-hosted WordPress plugin which features control panel for centralized WordPress management of sites within your network.
When you install the Server plugin you are ready to go - no additional installations, configurations and setups are required. This means that the data governance is entirely under your control.

## Network setup - as easy as 1, 2, 3 ##

You can setup your WP Juggler Network in three simple steps:

1. Install WP Juggler Server Plugin on one of your WordPress sites
2. Install WP Juggler Client plugins on sites you want to manage
3. Register your sites to the server in a single click of a button

## Getting Started Guide ##

### Install plugins ###
1. Download **wp-juggler-server.zip** file from the [latest release](https://github.com/boldthemes/wp-juggler-server/releases/latest) of WP Juggler Server plugin.
2. Install and Activate WP Juggler Server plugin in your WordPress instance which will hold the control panel for management of all other sites in your network.
3. Download **wp-juggler-client.zip** file from the [latest release](https://github.com/boldthemes/wp-juggler-client/releases/latest) of WP Juggler Client plugin.
4. Install and Activate WP Juggler Client plugin in WordPress instance you want to control remotely.

### Add site and register it ###
5. Navigate to 

### Fetch your first data ###

### Enable remote login ###

### Setup your crons ###

## ToDo List ##

1. Alert email templates
2. Plugin installation (both locally hosted and from WordPress.org)
3. Vulnerability check of installed themes and WP Core
4. Checksum of themes from WordPress.org
5. Client Reports on Activities

If you have a feature proposal or and idea on how to make WP Juggler better and more useful, please use [Issues section](https://github.com/boldthemes/wp-juggler-server/issues). We will be glad to review them and to the list.