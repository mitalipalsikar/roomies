<?php
require_once "../../inc/init.php";

/*
This script processes the data sent from the complete register page.
It updates creates a profile for the current user and inserts the values.
*/

if(!LOGGED_IN)
{
  require_once '../../inc/html/notfound.php';
  exit();
}

//THE PROCESS STUFF
$id = $_SESSION['user']['id'];
$stmt = $con->prepare("SELECT profile_filter_id FROM rdetails WHERE profile_filter_id = $id");
$stmt->execute();
$stmt->bindColumn(1, $dbId);
$stmt->fetch();

// We check if all values are set. Most of these values are safe,
// because are coming from selects. We'll check the names.
if((isset($_POST['first_name'],$_POST['last_name'],$_POST['b_year'],
         $_POST['b_month'],$_POST['b_day'],$_POST['country'],
         $_POST['language'],$_POST['gender'],$_POST['randomKey'])) 
  && ($_SESSION['randomKey'] == $_POST['randomKey']) && (!$stmt->rowCount()))
{
  // Get the values from POST
  $firstName = htmlentities($_POST['first_name']);
  $lastName = htmlentities($_POST['last_name']);
  $bYear = htmlentities($_POST['b_year']);
  $bMonth = htmlentities($_POST['b_month']);
  $bDay = htmlentities($_POST['b_day']);
  $country = htmlentities($_POST['country']);
  $language = htmlentities($_POST['language']);
  $gender = htmlentities($_POST['gender']);

  // Check if the ID exists. If not, it must be a problem
  $stmt = $con->prepare("SELECT user_id FROM rusers WHERE user_id = $id");
  $stmt->execute();
  $stmt->bindColumn(1, $dbId);
  $stmt->fetch();
  if(!$stmt->rowCount())
  {
    // There was a problem
    require_once __ROOT__."/inc/html/problem.php";
    exit();
  }

  // Format the birthday
  if($bDay < 10)
  {
    $bDay = "0".$bDay;
  }
  if($bMonth < 10)
  {
    $bMonth = "0".$bMonth;
  }
  $birthday = $bYear."-".$bMonth."-".$bDay;

  // Get the values in ints from mapping
  $stmt = $con->prepare("SELECT filter_value FROM rfiltersmap WHERE map_country = '$country'");
  $stmt->execute();
  $stmt->bindColumn(1, $mapCountry);
  $stmt->fetch();
  $stmt = $con->prepare("SELECT filter_value FROM rfiltersmap WHERE map_language = '$language'");
  $stmt->execute();
  $stmt->bindColumn(1, $mapLanguage);
  $stmt->fetch();
  $stmt = $con->prepare("SELECT filter_value FROM rfiltersmap WHERE map_gender = '$gender'");
  $stmt->execute();
  $stmt->bindColumn(1, $mapGender);
  $stmt->fetch();

  // Insert those values in rdetails
  $stmt = $con->prepare("INSERT INTO rdetails (profile_filter_id, first_name, last_name, birthday, country, language, gender)
                          VALUES ($id, '$firstName', '$lastName', '$birthday', '$mapCountry', '$mapLanguage', '$mapGender')");
  $stmt->execute();

  $stmt = null;
  unset($_SESSION['notComplete']);
  header("Location: ../");
  exit();
}
else if($stmt->rowCount())
{
  header("Location: ../");
  exit();
}
else if(isset($_POST['randomKey']))
{
  header("Location: ./?error");
  exit();
}



// THE DISPLAY PAGE
// Check whether the user has completed his profile

// Logout
if(isset($_GET['logout']))
{
  session_destroy();
  header("Location: ../");
  exit();
}
echo $_SESSION['randomKey'] == $_POST['randomKey'];
// Generate random key, needed for accessing the process script
$randKey = mt_rand();
if(!isset($_SESSION['randomKey']))
{
  $_SESSION['randomKey'] = $randKey;
}

$title = "Complete Registration";
$dots = "../";
?>
<?php require_once __ROOT__."/inc/html/head.php";?>
    <!--header-->
    <?php require_once __ROOT__."/inc/html/header.".$ioStatus.".php";?>
    <div class="main">
      <div id="mandatory_details" class="box">
        <div class="box-padding">
          <h2 id="Complete_registration" class="h2">
            Complete registration
          </h2>
          <p>
            The following details are mandatory for finishing your registration.
          </p>
          <div id="error" <?php echo (isset($_GET['error']))?"":"style='display:none;'"?>>
            <p style="color: red;">
              You must complete all fields before continuing.
            </p>
          </div>
          <form action="" name="details" method="POST">
            <div>
              <input class="input" type="text" required="" title="2 to 20 characters" placeholder="First/Given Name" name="first_name"></input>
              <input class="input input" type="text" required="" title="2 to 20 characters" placeholder="Last/Family Name" name="last_name"></input>
            </div>
            <div>
                <span>
                  <p>
                    Birthday:
                  </p>
                </span>
              <select class="select has-submit" id="byear" name="b_year">
                <option class="option" value="" selected="">Select year</option>
              </select>
              <select class="select has-submit" id="bmonth" name="b_month">
                <option class="option" value="" selected="">Select month</option>
              </select>
              <select class="select has-submit" id="bday" name="b_day">
                <option class="option" value="" selected="">Select day</option>
              </select>
            </div>
            <div>
              <span>
                <p>
                  Nationality and language preference:
                <p>
              </span>
              <select class="select has-submit" name="country">
                <option class="option" value="" selected="">Select country</option>
                <?php listCountryOptions();?>
              </select>
              <select class="select has-submit" name="language">
                <option class="option" value="" selected="">Select language</option>
                <?php listLanguageOptions();?>
              </select>
            </div>
            <div>
              <span>
                <p>
                  I identify my gender as:
                <p>
              </span>
              <select class="select has-submit" name="gender">
                <option class="option" value="">Select gender</option>
                <option class="option" value="man">Man</option>
                <option class="option" value="woman">Woman</option>
                <option class="option" value="trans">Trans*</option>
              </select>
            </div>
            <input type="hidden" name="randomKey" value="<?php echo $_SESSION['randomKey'];?>"></input>
            <input class="input-button block" type="submit" value="Submit"></input>
          </form>
        </div>
      </div>
      <div id="optional_details" class="box" style="display: none;">
        <?php require_once "optionalDetails.php";?>
      </div>
      <!--Scripts-->
      <script type="text/javascript" src="../media/js/jquery.min.js"></script>
      <script type="text/javascript" src="../media/js/birthday.js"></script>
<?php require_once __ROOT__."/inc/html/footer.php";?>
<?php
function listCountryOptions()
{
  // We have the array by courtesy of user DHS(David Haywood Smith) from GitHub
  $countries = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
  foreach($countries as $country)
  {
    echo "<option class='option' value='$country'>$country</option>";
  }
}
function listLanguageOptions()
{
  $language_codes = array('en' => 'English' , 'aa' => 'Afar' , 'ab' => 'Abkhazian' , 'af' => 'Afrikaans' , 'am' => 'Amharic' , 'ar' => 'Arabic' , 'as' => 'Assamese' , 'ay' => 'Aymara' , 'az' => 'Azerbaijani' , 'ba' => 'Bashkir' , 'be' => 'Byelorussian' , 'bg' => 'Bulgarian' , 'bh' => 'Bihari' , 'bi' => 'Bislama' , 'bn' => 'Bengali/Bangla' , 'bo' => 'Tibetan' , 'br' => 'Breton' , 'ca' => 'Catalan' , 'co' => 'Corsican' , 'cs' => 'Czech' , 'cy' => 'Welsh' , 'da' => 'Danish' , 'de' => 'German' , 'dz' => 'Bhutani' , 'el' => 'Greek' , 'eo' => 'Esperanto' , 'es' => 'Spanish' , 'et' => 'Estonian' , 'eu' => 'Basque' , 'fa' => 'Persian' , 'fi' => 'Finnish' , 'fj' => 'Fiji' , 'fo' => 'Faeroese' , 'fr' => 'French' , 'fy' => 'Frisian' , 'ga' => 'Irish' , 'gd' => 'Scots/Gaelic' , 'gl' => 'Galician' , 'gn' => 'Guarani' , 'gu' => 'Gujarati' , 'ha' => 'Hausa' , 'hi' => 'Hindi' , 'hr' => 'Croatian' , 'hu' => 'Hungarian' , 'hy' => 'Armenian' , 'ia' => 'Interlingua' , 'ie' => 'Interlingue' , 'ik' => 'Inupiak' , 'in' => 'Indonesian' , 'is' => 'Icelandic' , 'it' => 'Italian' , 'iw' => 'Hebrew' , 'ja' => 'Japanese' , 'ji' => 'Yiddish' , 'jw' => 'Javanese' , 'ka' => 'Georgian' , 'kk' => 'Kazakh' , 'kl' => 'Greenlandic' , 'km' => 'Cambodian' , 'kn' => 'Kannada' , 'ko' => 'Korean' , 'ks' => 'Kashmiri' , 'ku' => 'Kurdish' , 'ky' => 'Kirghiz' , 'la' => 'Latin' , 'ln' => 'Lingala' , 'lo' => 'Laothian' , 'lt' => 'Lithuanian' , 'lv' => 'Latvian/Lettish' , 'mg' => 'Malagasy' , 'mi' => 'Maori' , 'mk' => 'Macedonian' , 'ml' => 'Malayalam' , 'mn' => 'Mongolian' , 'mo' => 'Moldavian' , 'mr' => 'Marathi' , 'ms' => 'Malay' , 'mt' => 'Maltese' , 'my' => 'Burmese' , 'na' => 'Nauru' , 'ne' => 'Nepali' , 'nl' => 'Dutch' , 'no' => 'Norwegian' , 'oc' => 'Occitan' , 'om' => '(Afan)/Oromoor/Oriya' , 'pa' => 'Punjabi' , 'pl' => 'Polish' , 'ps' => 'Pashto/Pushto' , 'pt' => 'Portuguese' , 'qu' => 'Quechua' , 'rm' => 'Rhaeto-Romance' , 'rn' => 'Kirundi' , 'ro' => 'Romanian' , 'ru' => 'Russian' , 'rw' => 'Kinyarwanda' , 'sa' => 'Sanskrit' , 'sd' => 'Sindhi' , 'sg' => 'Sangro' , 'sh' => 'Serbo-Croatian' , 'si' => 'Singhalese' , 'sk' => 'Slovak' , 'sl' => 'Slovenian' , 'sm' => 'Samoan' , 'sn' => 'Shona' , 'so' => 'Somali' , 'sq' => 'Albanian' , 'sr' => 'Serbian' , 'ss' => 'Siswati' , 'st' => 'Sesotho' , 'su' => 'Sundanese' , 'sv' => 'Swedish' , 'sw' => 'Swahili' , 'ta' => 'Tamil' , 'te' => 'Tegulu' , 'tg' => 'Tajik' , 'th' => 'Thai' , 'ti' => 'Tigrinya' , 'tk' => 'Turkmen' , 'tl' => 'Tagalog' , 'tn' => 'Setswana' , 'to' => 'Tonga' , 'tr' => 'Turkish' , 'ts' => 'Tsonga' , 'tt' => 'Tatar' , 'tw' => 'Twi' , 'uk' => 'Ukrainian' , 'ur' => 'Urdu' , 'uz' => 'Uzbek' , 'vi' => 'Vietnamese' , 'vo' => 'Volapuk' , 'wo' => 'Wolof' , 'xh' => 'Xhosa' , 'yo' => 'Yoruba' , 'zh' => 'Chinese' , 'zu' => 'Zulu' , );

  foreach ($language_codes as $language) 
  {
    echo "<option class='option' value='$language'>$language</option>";
  }
}
?>