# Nahoni
PHP library to manage session variables via database. PHP normally stores session data as an assembly of delimited variables housed in serialized text files. This system is adequate for most single server applications, but quickly falls apart when greater scaling is required. It is also a difficult system to manage and does not lend well to organization, cleanup, and debugging.

Fortunately, PHP allows replacement of its built in functionality via class file overrides. Nahoni (named for the [Nahoni Mountain Range](http://www.geodata.us/canada_names_maps/maps.php?featureid=KAENM&f=312)) does just that. By leveraging PHP’s class overrides, there is no need for further code modifications in your application once Nahoni is installed. You may continue to use the <code>$_SESSION[]</code> methods as before - the session data will be routed to and from your RDBMS of choice.

Nohoni requires the [Yukon database library](https://github.com/DCurrent/Yukon) . Both are meant for use with MSSQL, but may be easily modified to accommodate another RDBMS if desired. 

# Install
Note these instructions assume you have already installed the [Yukon database library](https://github.com/DCurrent/Yukon) and have registered an autoload class.

1. Download and extract package.
1. Locate _database_ folder and run the enclosed scripts with your RDBMS. This will create the needed table and stored procedures.
1. Modify <code>\dc\yukon\Database()</code> path in _class Session_ constructor to suit your application file tree.
1. All PHP session methods are now overridden with the Nahoni Library. Start a PHP session and set a session variable. You should be able to locate the session as a table entry in your RDBMS.

