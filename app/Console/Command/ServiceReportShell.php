<?php
//app/Console/cake ServiceReport report "RCIAM User Community" "RCIAM IAM Notifications" "RCIAM IAM Login Report" "Dear IAM Service owner,\nAttached you will find the Login Report for the period (START DATE) to (END DATE).\n\nThank you,\nRCIAM IAM Support Team" "service_report.csv"

App::uses('CakeEmail', 'Network/Email');

class ServiceReportShell extends AppShell
{

  /**
   * @var string
   */
  private $logins_query = "SELECT count(DISTINCT hasheduserid) as unique_logins,"
  . " sum(count) as total_logins, country,"
  . " countrycode, min(date) as min_date,"
  . " max(date) as max_date"
  . " FROM statistics_country_hashed"
  . " WHERE service = ':service_id:'"
  . " AND country != 'Unknown'"
  . " GROUP BY country, countrycode"
  . " order by country asc;";

  /**
   * @var string[]
   */
  public $uses = array(
    'EmailAddress',
  );

  /**
   * @var null
   */
  private $Email = null;

  /**
   * @var null
   */
  private $fromTitle = null;

  /**
   * @var int
   */
  private $wait_sec = 5;

  /**
   * @var null
   */
  private $subject = null;

  /**
   * @var null
   */
  private $message_body = null;

  /**
   * @var null
   */
  private $co_name = null;

  /**
   * @var string[]
   */
  private $from = array(
    'noreply@faai.grnet.gr' => 'RCIAM AAI Notifications',
  );

  /**
   * @var null
   */
  private $csvfile = null;

  /**
   * @var null
   */
  private $csv_file_path = null;

  /**
   *
   */
  public function main()
  {
    $command = null;
    if (!empty($this->args[0])) {
      $this->Email     = new CakeEmail('default');
      $this->co_name   = !empty($this->args[1]) ? $this->args[1] : "";
      $this->fromTitle = !empty($this->args[2]) ? $this->args[2] : "";
      if (!empty($this->fromTitle)) {
        $this->from['noreply@faai.grnet.gr'] = $this->fromTitle;
      }
      $this->subject      = !empty($this->args[3]) ? $this->args[3] : "Notification";
      $this->message_body = !empty($this->args[4]) ? $this->args[4] : "";
      $this->csvfile      = $this->args[5];
      // Execute requested action
      $command = $this->args[0];
      $fn      = 'execute_' . $command;
      if (method_exists($this, $fn)) {
        $this->$fn();
      } else {
        $this->out('This command does not exist.');
        exit;
      }
    } else {
      $this->out('Please provide action');
    }
  }

  /**
   *
   */
  public function execute_report()
  {
    $dbc = $this->EmailAddress->getDataSource();
    // Parse the csv file to an array
    $data_array = $this->parsecsv_toarray($this->csvfile);
    // Find data per entry. If any
    foreach ($data_array as $service) {
      if (empty($service['Service Identifier'])
        || empty($service['Service Contact'])) {
        continue;
      }
      $this->out("Data: " . print_r($service, true));
      $toEmails = explode("\n", $service['Service Contact']);
      // Construct my query
      $this_service_tmp_query = str_replace(":service_id:", $service['Service Identifier'], $this->logins_query);
      $this->out("Query: " . $this_service_tmp_query);
      // Get the results
      $results = $this->EmailAddress->query($this_service_tmp_query);
      $this->out("Results: " . print_r($results, true));
      if (empty($results)) {
        continue;
      }
      // Create the csv file
      $tmpfname     = tempnam("/tmp", "service_provider_login_data");
      $this->create_tmp_csv_file($results, $tmpfname);
      // Send to my email
      $this->sendEmail(
        $this->from,
        $toEmails,
        $this->subject . " (" . $service['Service Identifier'] . ")",
        $this->message_body,
        $tmpfname
      );
      unlink($tmpfname);
    }
  }

  /**
   * @param $data
   *
   * @return false|string
   */
  public function create_tmp_csv_file($data, $tmpfname)
  {
    $headers = array();
    $fp      = null;
    if (!$fp = fopen($tmpfname, 'w+')) {
      $this->out("Could not open temp file.");
      return false;
    }

    foreach ($data as $row) {
      // Open temp file pointer
      if (empty($headers)) {
        $headers =  array_map(
          static function($field) {
            return Inflector::humanize($field);
          },
          array_keys($row[0]));
        fputcsv($fp, $headers);
      }
      //$this->out("Raw Row: " . print_r($row[0], true));
      //$this->out("Row: " . print_r(array_values($row[0]), true));
      fputcsv($fp, array_values($row[0]));
    }
    fclose($fp);
  }

  /**
   * @param $file
   *
   * @return array
   */
  public function parsecsv_toarray($file)
  {
    $parsed_data   = array();
    $headers       = null;
    $csv_file_path = $this->getFilePath($this->csvfile);
    $this->out("CSV File path : " . $csv_file_path);
    // Open the file for reading
    if (($h = fopen($csv_file_path, "r")) !== false) {
      // Each line in the file is converted into an individual array that we call $data
      // The items of the array are comma separated
      while (($data = fgetcsv($h, 4000, ",")) !== false) {
        if (empty($headers)) {
          // These are the headers
          $headers = $data;
          continue;
        }
        // Each individual array is being pushed into the nested array
        $parsed_data[] = array_combine($headers, $data);
      }
      fclose($h);
    }

    $this->out("Parsed data: " . print_r($parsed_data, true));
    return $parsed_data;
  }

  /**
   * @param $fname
   *
   * @return array|false|string|string[]|null
   */
  public function getFilePath($fname)
  {
    $raw_output = shell_exec("find ./ -name '" . $fname . "' -type f|xargs realpath");

    // Remove new lines
    return str_replace(array("\n\r", "\n", "\r"), "", $raw_output);
  }


  /**
   * @param $fromMail
   * @param $toMail
   * @param $subject
   * @param $messageBody
   * @param $attachment
   */
  public function sendEmail(
    $fromMail,
    $toMail,
    $subject,
    $messageBody,
    $attachment
  ) {
    $this->out('Sending email to:' . print_r($toMail, true));
    $this->Email->from($fromMail)
      ->emailFormat('both')
      ->to($toMail)
      ->subject($subject)
      ->template('custom', 'basic')
      ->viewVars(array('text' => $messageBody))
      ->attachments(
        array(
          "report" => array(
            "file"      => $attachment,
            "mimetype"  => "text/csv",
          ),
        )
      );
    $this->Email->send();
    $this->out('Wait ' . $this->wait_sec . 'sec ...');
    sleep($this->wait_sec);
  }

}
