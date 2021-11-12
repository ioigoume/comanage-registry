<?php
// app/Console/cake ServiceReport report "RCIAM Community" \
//                                       "RCIAM AAI Notifications" \
//                                       "RCIAM AAI Service Login Report for (@INSTALLATION) (M6-M10)" \
//                                       "Dear AAI Service owner,\nAttached you will find the Login Report for the period 1/6/2021 to 31/10/2021.\nInstallation: (@INSTALLATION)\nService ID: (@SERVICEID)n\nThank you,\nRCIAM AAI Support Team" \
//                                       "service_report.csv"

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
  . " AND date < '2021-11-01'"
  . " AND date > '2021-05-31'"
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
  private $wait_sec = 3;

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


  private function substitute($message, $input) {
    $substitutions = array(
      'INSTALLATION' => $input['Installation'],
      'SERVICEID' => $input['Service Identifier']
    );

    foreach ($substitutions as $key => $value) {
      $message = str_replace('(@' . $key . ')', $value, $message);
    }
    return $message;
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

      // Substitute placeholders
      $subject = $this->substitute($this->subject, $service);
      $message_body = $this->substitute($this->message_body, $service);

      $this->out("Subject: " . $subject);
      $this->out("Body: " . $message_body);

      // $this->out("Service Data: " . print_r($service, true));
      $toEmails = explode("\n", $service['Service Contact']);
      // Construct my query
      $this_service_tmp_query = str_replace(":service_id:", $service['Service Identifier'], $this->logins_query);
      $this->out("Query: " . $this_service_tmp_query);
      // Get the results
      $results = $this->EmailAddress->query($this_service_tmp_query);
      //$this->out("Results: " . print_r($results, true));
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
        $subject,
        $message_body,
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
      // $this->out("Raw Row: " . print_r($row[0], true));
      // $this->out("Row: " . print_r(array_values($row[0]), true));
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
      ->cc('faai@grnet.gr', 'faai')
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
    $this->out('<info>Wait ' . $this->wait_sec . 'sec ...</info>');
    sleep($this->wait_sec);
  }

}
