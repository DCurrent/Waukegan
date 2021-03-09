# Nahoni
PHP library to handle session variables through a Relational Database Management System (RDBMS). PHP normally stores session data as an assembly of delimited variables housed in serialized text files on the host server. This system is adequate for most single server applications, but quickly falls apart when greater scaling is required. It is also a difficult system to manage and does not lend well to organization, cleanup, and debugging.

Fortunately you may replace the default session functionality via class file overrides. Nahoni (named for the [Nahoni Mountain Range](http://www.geodata.us/canada_names_maps/maps.php?featureid=KAENM&f=312)) does just that. By leveraging PHP’s class overrides, there is no need for further code modifications in your application once Nahoni is installed. You may continue to use the <code>$_SESSION[]</code> methods as before - the session data will be routed to and from your RDBMS of choice.

Nahoni utilizes [PHP Data Objects (PDO)](https://www.php.net/manual/en/book.pdo.php) for database handling. The underlying code is written for Microsoft SQL Server but is easily adaptable to fit any common RDBMS.  

# Install
Note these instructions assume you have already configured [PDO]((https://www.php.net/manual/en/book.pdo.php) for your RDBMS.

1. Download and extract package.
1. Place extracted files in your web application file tree: {your parent location}/dc/nahoni
1. Locate _database_ folder and run the enclosed scripts with your RDBMS. This will create the needed table and stored procedures.

# Use
1. Initialize a PDO object.
1. Initialize {parent directory}\dc\nahoni\SessionConfig().
1. Use sessionConfig::set_database(PDO Object) to supply Nahoni with your PDO database handler.
1. Initialize {parent directory}\dc\nahoni\Session(SessionConfig). 
1. Call the PHP native function session_set_save_handler(SessionHandler) where SessionHandler is the Nahoni session object.
1. All PHP session methods are now overridden with the Nahoni Library. Start a PHP session and set a session variable. You should be able to locate the session as a table entry in your RDBMS.

```	
// First you need an active PDO connection.

$dsn = 'sqlsrv:Server=RDBMS_HOSTNAME;Database=DATABASE_NAME';
$user = 'DATABASE_USER_NAME';
$password = 'DATABASE_USER_PASSWORD';

$dbh_pdo_connection = new \PDO($dsn, $user, $password);

// Initialize the Nahoni SessionConfig object to
// set up options. The database connection is
// required.

$nahoni_config = new \dc\nahoni\SessionConfig();
$nahoni_config->set_database($dbh_pdo_connection);

// Start the Nahoni Session handler, and then pass
// it to PHP's session handler function. This replaces
// the native PHP session handling with Nahoni.

$session_handler = new \dc\nahoni\Session($nahoni_config);
session_set_save_handler($session_handler, TRUE);

// Start the session.

session_start();

// Write and read some session data. You should
// see this data updated in the RDBMS table.

$_SESSION['TEST_SESSION_VAR'] = 'Hello world';
echo $_SESSION['TEST_SESSION_VAR'];
```

