<script setup>
import { useWpjsStore } from "./store.js";
import { onMounted, computed, ref } from "vue";
import { useQueryClient, useQuery, useMutation } from "@tanstack/vue-query";

const store = useWpjsStore();

const search = ref("");

const dialogInner = ref(false);
const vulnerabilitiesItem = ref(null);

const data = [
  {
    "File": "example-notice/example-notice.php",
    "Checksum": true,
    "Wporg": false,
    "WpJuggler": true,
    "Name": "Example Notice",
    "Slug": "example-notice",
    "Title": "Example Notice",
    "Active": true,
    "Author": "BoldThemes",
    "Update": false,
    "Network": false,
    "Version": "1.0",
    "AuthorURI": "https://profiles.wordpress.org/boldthemes/",
    "PluginURI": "https://www.bold-themes.com",
    "UpdateURI": "",
    "AuthorName": "BoldThemes",
    "DomainPath": "",
    "RequiresWP": "",
    "TextDomain": "example-notice",
    "Description": "Displays a sample admin notice once for admins. (Requires PHP 5.3+.)",
    "RequiresPHP": "",
    "UpdateVersion": "",
    "RequiresPlugins": "",
    "Vulnerabilities": []
  },
  {
    "File": "contact-form-7/wp-contact-form-7.php",
    "Checksum": false,
    "Wporg": true,
    "WpJuggler": false,
    "Name": "Contact Form 7",
    "Slug": "contact-form-7",
    "Title": "Contact Form 7",
    "Active": false,
    "Author": "Takayuki Miyoshi",
    "Update": true,
    "Network": true,
    "Version": "4.0.0",
    "AuthorURI": "https://ideasilo.wordpress.com/",
    "PluginURI": "https://contactform7.com/",
    "UpdateURI": "",
    "AuthorName": "Takayuki Miyoshi",
    "DomainPath": "",
    "RequiresWP": "6.3",
    "TextDomain": "contact-form-7",
    "Description": "Just another contact form plugin. Simple but flexible.",
    "RequiresPHP": "7.4",
    "UpdateVersion": "5.9.8",
    "RequiresPlugins": "",
    "Vulnerabilities": [
      {
        "name": "Contact Form 7 [contact-form-7] < 5.3.2",
        "uuid": "69f4e580612c835bfea455eb87c0aa010b02c26489b2edccc6a5d9b1154113dc",
        "impact": {
          "cwe": [
            {
              "cwe": "CWE-434",
              "name": "Unrestricted Upload of File with Dangerous Type",
              "description": "The product allows the upload or transfer of dangerous file types that are automatically processed within its environment."
            }
          ]
        },
        "source": [
          {
            "id": "CVE-2020-35489",
            "date": "2020-12-17",
            "link": "https://www.cve.org/CVERecord?id=CVE-2020-35489",
            "name": "CVE-2020-35489",
            "description": "[en] The contact-form-7 (aka Contact Form 7) plugin before 5.3.2 for WordPress allows Unrestricted File Upload and remote code execution because a filename may contain special characters."
          },
          {
            "id": "7391118e-eef5-4ff8-a8ea-f6b65f442c63",
            "date": null,
            "link": "https://wpscan.com/vulnerability/7391118e-eef5-4ff8-a8ea-f6b65f442c63",
            "name": "Contact Form 7 &lt; 5.3.2 - Unrestricted File Upload",
            "description": "The popular WordPress plugin, Contact Form 7 was found to be vulnerable to Unrestricted File Upload."
          },
          {
            "id": "c4eafdf392908e880ec6b4c91d37e8413a7510d8",
            "date": "2020-12-17",
            "link": "https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/contact-form-7/contact-form-7-531-arbitrary-file-upload-via-bypass",
            "name": "Contact Form 7 <= 5.3.1 - Arbitrary File Upload via Bypass",
            "description": "The Contact Form 7 plugin for WordPress is vulnerable to arbitrary file uploads in versions up to 5.3.2. This is due to the fact that the plugin allows filenames to contain special characters which may make extension filter evasion possible on certain configurations. Our team was not able to reproduce this issue which leads us to believe there is a high attack complexity or special configuration requirement."
          }
        ],
        "operator": {
          "closed": "0",
          "unfixed": "0",
          "max_version": "5.3.2",
          "min_version": null,
          "max_operator": "lt",
          "min_operator": null
        },
        "description": null
      },
      {
        "name": "Contact Form 7 [contact-form-7] < 5.0.4",
        "uuid": "bf43a61e8ad7b51c20303b493f392c94d4556dceedb663862e26bc996b3caaab",
        "impact": [],
        "source": [
          {
            "id": "CVE-2018-20979",
            "date": "2019-08-22",
            "link": "https://www.cve.org/CVERecord?id=CVE-2018-20979",
            "name": "CVE-2018-20979",
            "description": "[en] The contact-form-7 plugin before 5.0.4 for WordPress has privilege escalation because of capability_type mishandling in register_post_type."
          },
          {
            "id": "af945f64-9ce2-485c-bf36-c2ff59dc10d5",
            "date": null,
            "link": "https://wpscan.com/vulnerability/af945f64-9ce2-485c-bf36-c2ff59dc10d5",
            "name": "Contact Form 7 &lt;= 5.0.3 - register_post_type() Privilege Escalation",
            "description": "According to the official release:\r\n\r\n&quot;A privilege escalation vulnerability has been found in Contact Form 7 5.0.3 and older versions. Utilizing this vulnerability, a logged-in user in the Contributor role can potentially edit contact forms, which only Administrator and Editor-role users are allowed to access by default. This issue has been reported by Simon Scannell from RIPS Technologies.\r\n\r\nTo minimize damage from possible attacks utilizing those vulnerabilities, Contact Form 7 5.0.4 and higher will restrict the local file attachment feature. More particularly, you will no longer be able to specify an absolute file path that refers to a file placed outside the wp-content directory. You can still specify files inside the wp-content directory with relative or absolute file paths, so all you need to change is the location of the attachment files.&quot;\r\n\r\nAccording to the changelog:\r\n\r\n&quot;Specifies the capability_type argument explicitly in the register_post_type() call to fix the privilege escalation vulnerability issue.&quot;"
          },
          {
            "id": "0013c8267f3da7ebe3f28e9a851deee6765d231d",
            "date": "2018-09-04",
            "link": "https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/contact-form-7/contact-form-7-503-authorization-bypass",
            "name": "Contact Form 7 <= 5.0.3 - Authorization Bypass",
            "description": "The Contact Form 7 plugin for WordPress is vulnerable to authorization bypass due to capability_type mishandling in register_post_type in versions up to, and including, 5.0.3. This makes it possible for authenticated attackers with contributor level privileges and above to modify contact forms and potential supply paths to sensitive files that make sensitive information disclosure possible."
          }
        ],
        "operator": {
          "closed": "0",
          "unfixed": "0",
          "max_version": "5.0.4",
          "min_version": null,
          "max_operator": "lt",
          "min_operator": null
        },
        "description": null
      },
      {
        "name": "Contact Form 7 [contact-form-7] < 5.3.2",
        "uuid": "a26423547b6bf8c59ad7d7259609e22c14f3bea37232df4091c06e8be64583df",
        "impact": [],
        "source": [
          {
            "id": "d6b8d2f948bb107e118f3cee56cc9df0dc13e184",
            "date": "2020-12-17",
            "link": "https://patchstack.com/database/vulnerability/contact-form-7/wordpress-contact-form-7-plugin-5-3-1-unrestricted-file-upload-vulnerability",
            "name": "WordPress Contact Form 7 plugin <= 5.3.1 - Unrestricted File Upload vulnerability",
            "description": "Unrestricted File Upload vulnerability found by Jinson Varghese Behanan in WordPress Contact Form 7 plugin (versions <= 5.3.1)."
          }
        ],
        "operator": {
          "closed": "0",
          "unfixed": "0",
          "max_version": "5.3.2",
          "min_version": null,
          "max_operator": "lt",
          "min_operator": null
        },
        "description": null
      },
      {
        "name": "Contact Form 7 [contact-form-7] < 5.0.4",
        "uuid": "46ad0a1e402e2b7fd43755090d353ebeeef7500e6ea531a129546012fed0ba84",
        "impact": [],
        "source": [
          {
            "id": "2838338b96418a4eda0472f209005117c380f4d5",
            "date": "2018-09-13",
            "link": "https://patchstack.com/database/vulnerability/contact-form-7/wordpress-contact-form-7-plugin-5-0-3-privilege-escalation-vulnerability",
            "name": "WordPress Contact Form 7 plugin <= 5.0.3 - Privilege Escalation vulnerability",
            "description": "Privilege Escalation vulnerability found by Simon Scannell in WordPress Contact Form 7 plugin (versions <= 5.0.3)."
          }
        ],
        "operator": {
          "closed": "0",
          "unfixed": "0",
          "max_version": "5.0.4",
          "min_version": null,
          "max_operator": "lt",
          "min_operator": null
        },
        "description": null
      },
      {
        "name": "Contact Form 7 [contact-form-7] < 5.8.4",
        "uuid": "3f3e7cdb5d815edb66f209dbb269f42df26ad83d9d3a69c7c3c4da5fce561d6d",
        "impact": {
          "cwe": [
            {
              "cwe": "CWE-434",
              "name": "Unrestricted Upload of File with Dangerous Type",
              "description": "The product allows the upload or transfer of dangerous file types that are automatically processed within its environment."
            }
          ],
          "cvss": {
            "a": "h",
            "c": "h",
            "i": "h",
            "s": "u",
            "ac": "h",
            "av": "n",
            "pr": "h",
            "ui": "n",
            "score": "6.6",
            "impact": "0.0",
            "vector": "CVSS:3.1/AV:N/AC:H/PR:H/UI:N/S:U/C:H/I:H/A:H",
            "version": "3.1",
            "severity": "m",
            "exploitable": "0.0"
          }
        },
        "source": [
          {
            "id": "CVE-2023-6449",
            "date": "2023-12-01",
            "link": "https://www.cve.org/CVERecord?id=CVE-2023-6449",
            "name": "CVE-2023-6449",
            "description": "[en] The Contact Form 7 plugin for WordPress is vulnerable to arbitrary file uploads due to insufficient file type validation in the 'validate' function and insufficient blocklisting on the 'wpcf7_antiscript_file_name' function in versions up to, and including, 5.8.3. This makes it possible for authenticated attackers with editor-level capabilities or above to upload arbitrary files on the affected site's server, but due to the htaccess configuration, remote code cannot be executed in most cases. By default, the file will be deleted from the server immediately. However, in some cases, other plugins may make it possible for the file to live on the server longer. This can make remote code execution possible when combined with another vulnerability, such as local file inclusion."
          },
          {
            "id": "24f57396df52355e47d4e49ae3c67ee7c109d4ff",
            "date": "2023-11-30",
            "link": "https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/contact-form-7/contact-form-7-583-authenticated-editor-arbitrary-file-upload",
            "name": "Contact Form 7 <= 5.8.3 - Authenticated (Editor+) Arbitrary File Upload",
            "description": "The Contact Form 7 plugin for WordPress is vulnerable to arbitrary file uploads due to insufficient file type validation in the 'validate' function and insufficient blocklisting on the 'wpcf7_antiscript_file_name' function in versions up to, and including, 5.8.3. This makes it possible for authenticated attackers with editor-level capabilities or above to upload arbitrary files on the affected site's server, but due to the htaccess configuration, remote code cannot be executed in most cases. By default, the file will be deleted from the server immediately. However, in some cases, other plugins may make it possible for the file to live on the server longer. This can make remote code execution possible when combined with another vulnerability, such as local file inclusion."
          },
          {
            "id": "e15c192035155221d4e0b807d25248131600495e",
            "date": "2023-12-01",
            "link": "https://patchstack.com/database/vulnerability/contact-form-7/wordpress-contact-form-7-plugin-5-8-3-authenticated-editor-arbitrary-file-upload-vulnerability",
            "name": "WordPress  Contact Form 7 Plugin  <= 5.8.3 is vulnerable to Arbitrary File Upload",
            "description": "Update the WordPress Add-on SweetAlert Contact Form 7 plugin to the latest available version (at least 5.8.4).\nIstván Márton discovered and reported this Arbitrary File Upload vulnerability in WordPress Contact Form 7 Plugin. This could allow a malicious actor to upload any type of file to your website. This can include backdoors which are then executed to gain further access to your website. This vulnerability has been fixed in version 5.8.4."
          },
          {
            "id": "70e21d9a-b1e6-4083-bcd3-7c1c13fd5382",
            "date": null,
            "link": "https://wpscan.com/vulnerability/70e21d9a-b1e6-4083-bcd3-7c1c13fd5382",
            "name": "Contact Form 7 &lt; 5.8.4 - Authenticated (Editor+) Arbitrary File Upload",
            "description": "The Contact Form 7 plugin for WordPress is vulnerable to arbitrary file uploads due to insufficient file type validation in the &#039;validate&#039; function and insufficient blocklisting on the &#039;wpcf7_antiscript_file_name&#039; function in versions up to, and including, 5.8.3. This makes it possible for authenticated attackers with editor-level capabilities or above to upload arbitrary files on the affected site&#039;s server, but due to the htaccess configuration, remote code cannot be executed in most cases. By default, the file will be deleted from the server immediately. However, in some cases, other plugins may make it possible for the file to live on the server longer. This can make remote code execution possible when combined with another vulnerability, such as local file inclusion."
          }
        ],
        "operator": {
          "closed": "0",
          "unfixed": "0",
          "max_version": "5.8.4",
          "min_version": null,
          "max_operator": "lt",
          "min_operator": null
        },
        "description": null
      },
      {
        "name": "Contact Form 7 [contact-form-7] < 5.9.2",
        "uuid": "f2293632631341a14596411858137750d6f77619f8f2bfdab04b9f95536a26e5",
        "impact": {
          "cwe": [
            {
              "cwe": "CWE-79",
              "name": "Improper Neutralization of Input During Web Page Generation ('Cross-site Scripting')",
              "description": "The product does not neutralize or incorrectly neutralizes user-controllable input before it is placed in output that is used as a web page that is served to other users."
            }
          ],
          "cvss": {
            "a": "n",
            "c": "l",
            "i": "l",
            "s": "c",
            "ac": "l",
            "av": "n",
            "pr": "n",
            "ui": "r",
            "score": "6.1",
            "impact": "0.0",
            "vector": "CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:C/C:L/I:L/A:N",
            "version": "3.1",
            "severity": "m",
            "exploitable": "0.0"
          }
        },
        "source": [
          {
            "id": "CVE-2024-2242",
            "date": "2024-03-13",
            "link": "https://www.cve.org/CVERecord?id=CVE-2024-2242",
            "name": "CVE-2024-2242",
            "description": "[en] The Contact Form 7 plugin for WordPress is vulnerable to Reflected Cross-Site Scripting via the ‘active-tab’ parameter in all versions up to, and including, 5.9 due to insufficient input sanitization and output escaping. This makes it possible for unauthenticated attackers to inject arbitrary web scripts in pages that execute if they can successfully trick a user into performing an action such as clicking on a link."
          },
          {
            "id": "a4133485452498aa7c096da4d284b3d6869ebf4b",
            "date": "2024-03-13",
            "link": "https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/contact-form-7/contact-form-7-59-reflected-cross-site-scripting",
            "name": "Contact Form 7 <= 5.9 - Reflected Cross-Site Scripting",
            "description": "The Contact Form 7 plugin for WordPress is vulnerable to Reflected Cross-Site Scripting via the ‘active-tab’ parameter in all versions up to, and including, 5.9 due to insufficient input sanitization and output escaping. This makes it possible for unauthenticated attackers to inject arbitrary web scripts in pages that execute if they can successfully trick a user into performing an action such as clicking on a link."
          },
          {
            "id": "e2dfb032e1438ea61faec1dccb16dfde131ec8b5",
            "date": null,
            "link": "https://patchstack.com/database/vulnerability/contact-form-7/wordpress-contact-form-7-plugin-5-9-reflected-cross-site-scripting-vulnerability",
            "name": "WordPress  Contact Form 7 Plugin    <= 5.9 is vulnerable to Cross Site Scripting (XSS)",
            "description": "Update the WordPress Contact Form 7 plugin to the latest available version (at least 5.9.2).\nAsaf Mozes discovered and reported this Cross Site Scripting (XSS) vulnerability in WordPress Contact Form 7 Plugin. This could allow a malicious actor to inject malicious scripts, such as redirects, advertisements, and other HTML payloads into your website which will be executed when guests visit your site.  This vulnerability has been fixed in version 5.9.2.\nHave additional information or questions about this entry? Get in touch."
          },
          {
            "id": "1c070a2c-2ab0-43bf-b10b-6575709918bc",
            "date": null,
            "link": "https://wpscan.com/vulnerability/1c070a2c-2ab0-43bf-b10b-6575709918bc",
            "name": "Contact Form 7 &lt; 5.9.2 - Reflected Cross-Site Scripting",
            "description": "The plugin does not sanitise and escape a parameter before outputting it back in the page, leading to a Reflected Cross-Site Scripting which could be used against administrators."
          }
        ],
        "operator": {
          "closed": "0",
          "unfixed": "0",
          "max_version": "5.9.2",
          "min_version": null,
          "max_operator": "lt",
          "min_operator": null
        },
        "description": null
      },
      {
        "name": "Contact Form 7 [contact-form-7] < 5.9.5",
        "uuid": "11deabaaffc704bdad7665508e0dd88de89a9b330115fdb1718a72104c302b1d",
        "impact": {
          "cwe": [
            {
              "cwe": "CWE-601",
              "name": "URL Redirection to Untrusted Site ('Open Redirect')",
              "description": "A web application accepts a user-controlled input that specifies a link to an external site, and uses that link in a Redirect. This simplifies phishing attacks."
            }
          ]
        },
        "source": [
          {
            "id": "CVE-2024-4704",
            "date": "2024-06-27",
            "link": "https://www.cve.org/CVERecord?id=CVE-2024-4704",
            "name": "CVE-2024-4704",
            "description": "[en] The Contact Form 7 WordPress plugin before 5.9.5 has an open redirect that allows an attacker to utilize a false URL and redirect to the URL of their choosing."
          },
          {
            "id": "9d7789cdb626ed7fcc9916f072bc7538770c205c",
            "date": "2024-06-27",
            "link": "https://patchstack.com/database/vulnerability/contact-form-7/wordpress-contact-form-7-plugin-5-9-5-unauthenticated-open-redirect-vulnerability",
            "name": "WordPress Contact Form 7 Plugin < 5.9.5 is vulnerable to Open Redirection",
            "description": "<p>WordPress Contact Form 7 Plugin < 5.9.5 is vulnerable to Open Redirection</p><p>Software: Contact Form 7</p><p>Link: https://wordpress.org/plugins/contact-form-7/#developers</p><p>Affected Version < 5.9.5</p><p>Fixed in version 5.9.5 </p>"
          },
          {
            "id": "1eb2c1adb2ea5a6fea057ea1092e7be5e98b776d",
            "date": "2024-06-05",
            "link": "https://www.wordfence.com/threat-intel/vulnerabilities/wordpress-plugins/contact-form-7/contact-form-7-594-unauthenticated-open-redirect",
            "name": "Contact Form 7 <= 5.9.4 - Unauthenticated Open Redirect",
            "description": "The Contact Form 7 plugin for WordPress is vulnerable to Open Redirect in all versions up to, and including, 5.9.4. This is due to insufficient validation on the redirect url supplied via accessing the contact form with a spoofed page. This makes it possible for unauthenticated attackers to redirect site users to potentially malicious sites if they can successfully trick them into performing an action."
          }
        ],
        "operator": {
          "closed": "0",
          "unfixed": "0",
          "max_version": "5.9.5",
          "min_version": null,
          "max_operator": "lt",
          "min_operator": null
        },
        "description": null
      }
    ]
  },
  {
    "File": "wp-juggler-client/wp-juggler-client.php",
    "Checksum": true,
    "Wporg": false,
    "WpJuggler": false,
    "Name": "WP Juggler Client",
    "Slug": "wp-juggler-client",
    "Title": "WP Juggler Client",
    "Active": true,
    "Author": "BoldThemes",
    "Update": false,
    "Network": true,
    "Version": "1.0.0",
    "AuthorURI": "https://wpjuggler.com",
    "PluginURI": "https://wpjuggler.com",
    "UpdateURI": "",
    "AuthorName": "BoldThemes",
    "DomainPath": "/languages",
    "RequiresWP": "",
    "TextDomain": "wp-juggler-client",
    "Description": "WP Juggler Client",
    "RequiresPHP": "",
    "UpdateVersion": "",
    "RequiresPlugins": "",
    "Vulnerabilities": []
  }
]

const headers = [
  { title: "Name", value: "Name", align: "start", sortable: true },
  {
    title: "Active",
    key: "active",
    align: "center",
    sortable: false,
  },
  { title: "Version", value: "Version", align: "center", sortable: true },
  {
    title: "Update",
    key: "update",
    align: "center",
    sortable: false,
  },
  {
    title: "Vulnerabilities",
    key: "vulnerabilities",
    align: "center",
    sortable: true,
  },
  {
    title: "Cheksum",
    key: "checksum",
    align: "start",
    sortable: true,
  },
  {
    title: "Actions",
    key: "actions",
    align: "start",
    sortable: true,
  },
];

const expanded = ref([]);

const dialog = ref(false);

const tab = ref(0);

function openVulnerabilities(item) {
  vulnerabilitiesItem.value = item
  dialogInner.value = true
}

</script>

<template>
  <div class="text-center pa-4">
    <v-dialog v-model="store.activatedThemes" transition="dialog-bottom-transition" fullscreen>
      <v-card>
        <v-toolbar>
          <v-btn icon="mdi-close" @click="store.activatedThemes = false"></v-btn>

          <v-toolbar-title>{{ store.activatedSite.title }}
          </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-toolbar-items> </v-toolbar-items>
        </v-toolbar>

        <v-card-text>
          <v-card>
            <v-tabs v-model="tab" bg-color="surface">
              <v-tab value="plugins">Plugins</v-tab>
              <v-tab value="themes">Themes</v-tab>
            </v-tabs>

            <v-card-text>
              <v-tabs-window v-model="tab">
                <v-tabs-window-item value="plugins">
                  <v-sheet class="pa-4 text-right mx-auto" elevation="0" width="100%" rounded="lg">
                    <div v-if="
                      store.activatedSite.wp_juggler_plugins_summary_timestamp
                    ">
                      <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                      {{
                        store.activatedSite.wp_juggler_plugins_summary_timestamp
                      }}
                      <v-btn class="ml-3 text-none text-caption">Refresh
                      </v-btn>
                    </div>

                    <div v-else>
                      <v-icon class="me-1 pb-1" icon="mdi-refresh" size="18"></v-icon>
                      Never
                      <v-btn class="ml-3 text-none text-caption">Refresh
                      </v-btn>
                    </div>
                  </v-sheet>
                  <v-divider></v-divider>

                  <v-sheet>
                    <v-spacer></v-spacer>

                    <v-text-field v-model="search" density="compact" label="Search" prepend-inner-icon="mdi-magnify"
                      variant="solo-filled" flat hide-details single-line></v-text-field>

                    <v-data-table v-model:search="search" :items="data" :headers="headers" item-key="id" show-select>
                      <template v-slot:item.active="{ item }">
                        <div v-if="item.Active">
                          <v-icon color="success" icon="mdi-check-bold" size="large" class="rm-4"></v-icon>
                        </div>
                      </template>

                      <template v-slot:item.update="{ item }">
                        <div v-if="item.Update">
                          <v-icon color="success" icon="mdi-check-bold" size="large" class="rm-4"></v-icon> {{
                            item.UpdateVersion }}
                        </div>
                      </template>

                      <template v-slot:item.vulnerabilities="{ item }">
                        <div v-if="item.Vulnerabilities.length > 0 && item.Wporg">
                          <v-icon color="error" icon="mdi-bug-check-outline" size="large" class="mr-1"></v-icon> {{
                            item.Vulnerabilities.length }}
                          <v-btn class="ml-3 text-none text-caption" @click="openVulnerabilities(item)">Details
                          </v-btn>
                        </div>
                        <div v-else-if="!item.Wporg">
                          <v-icon color="blue-lighten-5" icon="mdi-help" size="large" class="rm-4"></v-icon>
                        </div>
                      </template>

                      <template v-slot:item.checksum="{ item }">
                        <div v-if="!item.Vulnerabilities.Checksum && item.Wporg">
                          <v-icon color="error" icon="mdi-alert-outline" size="large" class="mr-1"></v-icon>
                        </div>
                        <div v-else-if="!item.Wporg">
                          <v-icon color="blue-lighten-5" icon="mdi-help" size="large" class="rm-4"></v-icon>
                        </div>
                      </template>

                      <template v-slot:item.actions="{ item }">
                        <v-btn v-if="item.Active" class="ml-3 text-none text-caption">Deactivate
                        </v-btn>
                        <v-btn v-if="!item.Active" class="ml-3 text-none text-caption">Activate
                        </v-btn>
                        <v-btn v-if="!item.Active && item.Network" class="ml-3 text-none text-caption">Network Activate
                        </v-btn>
                        <v-btn v-if="item.Update" color="#2196f3" variant="elevated"
                          class="text-none text-caption ml-3">Update
                        </v-btn>
                      </template>
                    </v-data-table>
                  </v-sheet>
                </v-tabs-window-item>

                <v-tabs-window-item value="themes"> Two </v-tabs-window-item>
              </v-tabs-window>
            </v-card-text>
          </v-card>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="dialogInner" min-width="600">

      <v-card>
        <v-toolbar>
          <v-btn icon="mdi-close" @click="dialogInner = false"></v-btn>

          <v-toolbar-title>List of vulnerabilities
          </v-toolbar-title>

        </v-toolbar>

        <v-card-text>
          <v-sheet v-for="vul in vulnerabilitiesItem.Vulnerabilities">
            <div><strong>{{ vul.name }}</strong></div>
            <div v-if="'cwe' in vul.impact" class="ml-6 mt-2">
              <div>
                {{ vul.impact.cwe[0].name }}
              </div>
              <div>
                {{ vul.impact.cwe[0].description }}
              </div>
            </div>

            <div class="mt-4 ml-6"><strong>Sources:</strong></div>

            <div v-for="src in vul.source" class="mt-2 ml-6">
              <div class="ml-4">
                {{ src.date }} - <a :href="src.link" target="_blank">{{ src.name }}</a>
              </div>
              <div class="ml-4">
                {{ src.description }}
              </div>
            </div>
            <v-divider class="mt-4 mb-4"></v-divider>
          </v-sheet>

        </v-card-text>

      </v-card>
    </v-dialog>
  </div>
</template>

<style></style>
