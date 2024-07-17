# Plot-data-management
Larp Information plugin for Wordpress and Airtable

# Creator
Ria 'Riffi' Böök, you can contat via email riffiria(at)gmail.com

# Purpose

This project provides a solution to show character and group specified information for players in a larp based on their character code and codes on items for example.

On first phase, this does only retrieving the data, updating the data will be done in Airtable. 

Tool handles also full visibility for GM's and possiblity show some information to all everybody.

Tool can be tested at: http://www.lasipallo.com/testworld/

# Approach

The project provides guidance for setting up the workspace. Plugin should work out of the box as long the workspace is done according the instructions.

It is implemented as a Wordpress Plugin which uses teh Wordpress preferred method wp_remote_get() rather than directly using curl or javascript. The plugin, as is, can be configured to work with a free Airtable account and customer Airtable table.


# Getting started

## Creating a free Airtable Account

If you already have an Airtable account, this step can be skipped.

If you don't already have an Airtable account, [Airtable](https://airtable.com/pricing) provide a free account option for 'individuals or teams just getting started with Airtable'.

Once you have signed up you will have access to an initial workspace usually named 'My First Workspace'. From here you can add a 'base' - Airtable jargon for a database.


## Creating a Base

This plugin requires specific Airtable structure:

1) Choose name for the workspace
2) Create Following blank tables with these specific Field names:
Table: Characters
Fields: Character (primary field), Groups (Linked to Groups table, to Group field), username (text)

Table: Groups
Fields: Group (primary field), Characters (Linked to Characters table, to Character field)

Table: Information
Fields: Description (Text field), Code (Text field), Characters (Linked to Characters table, to Character field), Groups (Linked to Groups table, to Group field), Add lookupfield to Groups table, refering to Characters field.



### Finding your account specific API documentation and PAT

Once you have created your Base and added the columns as specified, your API Documentation will be ready for use. It is important to note that the documentation is dynamically generated to represent your base and tables. It can be found with the Help | <> API Documentation.

Create PAT key with correct user rights.

### Finding your base ID

For this plugin you will need the specific Airtable reference to the table. This can be found from the API documentation. At the beginning of the documenation there is ID of the base. "The ID of this base is appAG8swVyRsR3hTQ." (That's dymmy ID, don't use that.)

## Setting up Wordpress

The following instructions assume that you have a functioning Wordpress instance and you have downloaded, installed and activated this plugin.

### Configuring the plugin

To configure the plugin you will require five pieces of information from Airtable:

1) Your Airtable PAT
2) Your Table ID
3) Group name for your GM group
4) Group name for your Everyone group 
5) Allow manualcharater name entering - By default Yes
6) Allow accessto GM data without Wordpress login - By default Yes

From with Admin | Plugins there will be an option 'Airtable game instructions database' that will enable you to enter the three pieces of information.

Note: The API PAT is not shown once entered and is encrypted within the database.

### Setting up Wordpress-Airtable users and permissions.

If you have not already invented and entered usernames for characters in the 'username' column of the Airtable Volunteers table, do it now.
If Wordpress login is used, then Wordpress loginname must match the database information

### Adding the [my-airtable] shortcode to a page of your choosing.

To generate the Airtable data within Wordpress, simply create a new Page, it can be titled anything you wish e.g. 'My Profile', and add the shortcode `[my-airtable]` to the page.

Publish the page.

### Testing

To test that everything is working and designed, navigate to the page you published. Enter some test data to Airtable and test the tool with different combinations.

# Known Limitations

At this time, within the plugin, there is no error handling for the calls from wordpress to the airtable api.

# Further Assistance

Few sources I found useful were: 

[stackoverflow](https://stackoverflow.com)

[community.airtable.com](https://community.airtable.com)

[Wordpress-Airtable](https://github.com/MyWebToolkit/Wordpress-Airtable)



