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

- Track [WP Health and WP Debug](https://wordpress.org/documentation/article/site-health-screen/) info for all your WordPress sites
- Seamlessly login into wp-admin panels of your sites with a single click
- Set up uptime monitor and receive alerts when your sites stop responding
- Manage your WordPress plugins - update, activate and deactivate them in bulk actions from a central point
- Host your own plugins in your network and update them as if they were hosted on wordpress.org
- For additional security, [verify checksums](https://developer.wordpress.org/cli/commands/plugin/verify-checksums/) of installed plugins, themes and WP Core and receive alerts on all failed checks
- Track [known vulnerabilities](https://www.wpvulnerability.com/) of current versions of your plugins and themes and avoid being hacked
- Manage and update themes from a central point
- Track WordPress wp-admin dashboard notices for all your WordPress sites
- Set granular user rights to control who sees what within your network

WP Juggler is fully compatible with WordPress multisite setups. 
It also makes TGMPA plugins fully integreted into WordPress update mechanism. 
This is important if you have premium themes (such as ones coming from ThemeForest) installed on your sites. Their packaged plugins will be treated the same way as all other plugins in your WordPress installations.

## How does it work - Main concepts ##

WP Juggler has two components:

- [WP Juggler Server plugin](https://github.com/boldthemes/wp-juggler-server)
- [WP Juggler Client plugin](https://github.com/boldthemes/wp-juggler-client)

WP Juggler Server is a self-hosted WordPress plugin which features control panel for centralized WordPress management of sites within your network.
When you install the Server plugin you are ready to go - no additional installations, configurations and setups are required. 
This also means that the data governance within your network is entirely under your control.

## Network setup - as easy as one, two, three ##

You can setup your WP Juggler Network in three simple steps:

1. Kickstart the process by installing the WP Juggler Server Plugin on one of your WordPress sites
2. Next, install WP Juggler Client plugins on sites you want to control and manage
3. Lastly, register and activate your sites on your server with a single mouse click 

## Getting Started Guide ##

### Install plugins ###
1. Download **wp-juggler-server.zip** file from the [latest release](https://github.com/boldthemes/wp-juggler-server/releases/latest) of WP Juggler Server plugin.
2. Install and Activate WP Juggler Server plugin on one of your WordPress sites. This site will serve as the primary control panel.
3. Download **wp-juggler-client.zip** file from the [latest release](https://github.com/boldthemes/wp-juggler-client/releases/latest) of WP Juggler Client plugin.
4. Install and Activate WP Juggler Client plugin on WordPress sites you want to control remotely.

### Add site and register it ###
5. Navigate to 

### Fetch your first data ###

### Enable remote login ###

### Setup your crons ###

## ToDo List ##

- [ ] Alert email templates
- [ ] Plugin installation (both locally hosted and from WordPress.org)
- [ ] Vulnerability check of installed themes and WP Core
- [ ] Checksum of themes from WordPress.org
- [ ] Client Reports on Activities

If you have a feature proposal or and idea on how to make WP Juggler better and more useful, please use [Issues section](https://github.com/boldthemes/wp-juggler-server/issues). We will be glad to review them and to the list.