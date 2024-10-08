# WP Juggler #

WP Juggler is your ultimate solution for effectively managing an unlimited portfolio of WordPress sites from a single, intuitive dashboard. 

WP Juggler helps you to enhance your operational efficiency, ensure your sites stay current with updates, and reclaim precious time to focus on more impactful projects. 

Being an open-source and completely free tool, WP Juggler is designed to revolutionize your WordPress maintenance experience without costing you a dime.

## The idea behind it ##

WP Juggler is a product of [BoldThemes'](https://bold-themes.com/) need to efficiently manage more than [100 demo sites](https://themeforest.net/user/boldthemes/portfolio) showcasing our premium WordPress themes and more than 200 websites resulting from our agency work.

Before the creation of WP Juggler, managing multiple sites was a chaotic, time-consuming process that often resulted in costly maintenance issues. The solutions available in the market were either inadquate, overpriced or fell short in efficient functionality. 

So we have created WP Juggler

![Screenshot of WP Juggler Control Panel](https://bold-themes.com/wp-content/wp-juggler-assets/wp-juggler-control-panel.png)

## Why choose WP Juggler? ##

There are more than few reasons to consider WP Juggler over existing commercial products.
With WP Juggler you can:

- Track [WP Health and WP Debug](https://wordpress.org/documentation/article/site-health-screen/) info for all your WordPress sites
- Seamlessly login into wp-admin panel of your sites with a single click
- Set up uptime monitor and receive alerts when your sites stop responding
- Manage your WordPress plugins - update, activate and deactivate them in bulk from a central point
- Host your own plugins in your network and update them as if they were hosted on wordpress.org
- For additional security, [verify checksums](https://developer.wordpress.org/cli/commands/plugin/verify-checksums/) of installed plugins, themes and WP Core and receive alerts on all failed checks
- Track [known vulnerabilities](https://www.wpvulnerability.com/) of current versions of your plugins and themes and avoid being hacked
- Manage and update themes from a central point
- Track WordPress wp-admin dashboard notices for all your WordPress sites
- Set granular user rights to control who sees what within your network

![Screenshot of WP Juggler Plugins screen](https://bold-themes.com/wp-content/wp-juggler-assets/wp-juggler-plugins.png)

WP Juggler is fully compatible with WordPress multisite setups.

It also makes TGMPA plugins fully integreted into WordPress update mechanism. 

This is important if you have premium themes (such as ones coming from ThemeForest) installed on your sites. Their packaged plugins will be treated the same way as all other plugins in your WordPress installations.

![Screenshot of WP Juggler Site Health Info](https://bold-themes.com/wp-content/wp-juggler-assets/wp-juggler-health.png)

## How does it work - Main concepts ##

WP Juggler has two components:

- WP Juggler Server plugin - this repository
- [WP Juggler Client plugin](https://github.com/boldthemes/wp-juggler-client)


> [!NOTE]
> WP Juggler Server is a self-hosted WordPress plugin which features control panel for centralized WordPress management of your sites.

> This means that the data governance within your network is entirely under your control.

## Network setup - as easy as one, two, three ##

You can setup your WP Juggler Network in three simple steps:

1. Kickstart the process by installing the WP Juggler Server Plugin on one of your WordPress sites
2. Install [WP Juggler Client plugins](https://github.com/boldthemes/wp-juggler-client) on sites you want to control and manage
3. Register and activate your sites on your server with a single mouse click 

## Getting Started Guide ##

### Install plugins ###
1. Download **wp-juggler-server.zip** file from the [latest release](https://github.com/boldthemes/wp-juggler-server/releases/latest) of **WP Juggler Server** plugin.
2. Install and Activate WP Juggler Server plugin on one of your WordPress sites. This site will serve as your control panel.
3. Download **wp-juggler-client.zip** file from the [latest release](https://github.com/boldthemes/wp-juggler-client/releases/latest) of **WP Juggler Client** plugin.
4. Install and Activate WP Juggler Client plugin on WordPress sites you want to monitor and manage remotely.

### Add new site ###
1. Navigate to **WP Juggler > Sites** in your server's wp-admin and click **Add New**
2. Enter the site name as title, enter the site url. 
3. Click **Assign Users** and add the users who will be able to manage the added site (add your user for start). 
4. Click **Save** and copy API Key to clipboard

### Activate new site ###
1. Navigate to **WP Juggler** screen in your client site's wp-admin and enter bith API Key and server's url
2. Click **Save Settings**. You will should see the message that your site is successfully activated 

![Screenshot of WP Juggler Site Edit Screen](https://bold-themes.com/wp-content/wp-juggler-assets/wp-juggler-site-edit.png)

### Fetch your first data ###
1. Navigate to  **WP Juggler > Control Panel** in your server's wp-admin and your newly activated site should appear in the list.
2. Click the arrow at the end of its row to expand the panel and click **Refresh All Site Data**
3. Once the refresh finishes you will be able to see the summary of the data retrieved from your site. 
4. Explore the available info by clicking buttons in the expansion panel

### Enable one-click login to wp-admin ###
1. Navigate to **WP Juggler > Sites** in your server's wp-admin and edit the desired site.
2. Check **Automatic Login**, enter **Remote Login Username** (username of the user you are going to log in on the target site) and click **Save**
3. On your client site, edit the User's profile and check **Enable auto login for this user**. 
4. We are all set. You can test the login by first logging out of the client site's wp-admin.  
4. Lastly, go to your control panel and click **wp-admin** button in your site's row. You should be automatically logged in as chosen user.

### Setup your crons ###
WP Juggler uses WP Cron system to automatically refresh remote site data. 

You can set the refresh frequency by navigating to **WP Juggler > Settings** in your server's wp-admin.

> [!IMPORTANT]
> The WordPress cron system is activated automatically based on a page view / init action. 
> This means that if you do not have visits on your server site, the WP Cron will not fire and no data will be automatically retreived. This includes uptime crons.

![Screenshot of WP Juggler Uptime screen](https://bold-themes.com/wp-content/wp-juggler-assets/wp-juggler-uptime.png)

### Solution for crons ###
Therefore it is best to set up a cronjob to call the wp-cron.php file in the root of your server WordPress install every 5 minutes, otherwise your scheduled tasks may not run correctly. 

Depending on your hosting environment, there is more than one way to do this.

Here is the general explanation from WordPress plugin handbook on how to this: [Hooking WP-Cron Into the System Task Scheduler](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/)

On most hostings you can do this on your hosting control panel or hosting dashboard. Here is the explanation from [SiteGround](https://eu.siteground.com/tutorials/wordpress/real-cron-job/). 

Contact your hosting support if needed.

![Screenshot of WP Juggler Dashboard Screen](https://bold-themes.com/wp-content/wp-juggler-assets/wp-juggler-crons.png)

## ToDo List ##

If you have a feature proposal or and idea on how to make WP Juggler better and more useful, please use [Issues section](https://github.com/boldthemes/wp-juggler-server/issues). We will be glad to review them and add them to the list.

Currenty opened development tasks:

- [ ] Alert email templates
- [ ] Remote plugin installation (both locally hosted and from WordPress.org)
- [ ] Vulnerability check of installed themes and WP Core
- [ ] Checksum verification of themes from WordPress.org
- [ ] PDF Reports on activites per site within a given time period