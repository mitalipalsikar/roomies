<?php
// Initialise the page, requiring the user to be logged in
// define('REQUIRE_SESSION', true);
require_once '../../inc/init.php';
// Temporary user array (supposed to get from /inc/init.php)
// Gonna just fill the values in as myself
$user = array(
    'name' =>    'Daniel Hodgson',
    'picture' => 'anonymous.jpg',
    'filters' => array(
        0 => 'English',
        1 => 'Atheism',
        2 => 'Unaffiliated',
        3 => 'Coffee',
        4 => false,
        // 6 => This person doesn't like or dislike trees.
    )
);

// Include the head of the page
$title = 'Search';
// Variable wich controls the relative path of /inc/media folder (used in head.php)
$dots = "../";
//include __ROOT__.'/inc/html/head.php';

// Include the header of the page
// We don't need to use $ioStatus here, since it MUST be "in"
//include __ROOT__.'/inc/html/header.in.php';

// While head.php and header.in.php haven't been made, I'll just do them here myself
// Made.
require_once __ROOT__."/inc/html/head.php";
require_once __ROOT__."/inc/html/header.$ioStatus.php";
?>
<!-- Main content -->
<div class="main">
<?php

// TODO: Output the top of the search page, with the ignore form
// If the user is ignoring some filters, put them into $ignore
if (isset($_POST['ignore']))
    $ignore = $_POST['ignore'];
else
    $ignore = array();

// TODO: The search algorithm, using filters array from $_POST and putting the
// results for each user into an array, $results, with:
// $results[$i]['id']          (their user id)
// $results[$i]['name']          (their name or "anonymous")
// $results[$i]['picture']       (their picture or "anonymous.jpg")
// $results[$i]['compatibility'] (the percent compatibility from 0.0 to 100.0)
// $results[$i]['filters']       (an array of the filters)
// $results[$i]['filters'][$j]['id']    (the id of the filter, to know what question to put with it)
// $results[$i]['filters'][$j]['value'] (true/false: whether filter $j matched)

// Output each result
// Temporary filters array (get from db)
$filters = array(
    array(
        'name'   => 'Native Language',
        'string' => 'This person&rsquo;s native language is'
    ),
    array(
        'name'   => 'Religious Views',
        'string' => 'This person&rsquo;s native language is'
    ),
    array(
        'name'   => 'Political Views',
        'string' => 'This person&rsquo;s native language is'
    ),
    array(
        'name'   => 'Favourite Hot Beverage',
        'string' => 'This person&rsquo;s native language is'
    ),
    array(
        'name'   => 'Drink Alcohol',
        'string' => 'This person&rsquo;s native language is'
    ),
    array(
        'name'   => 'Like Trees',
        'string' => 'This person&rsquo;s native language is'
    )
);
// Temporary results array
$results = array(
    array(
        'id'            => 34,
        'name'          => 'Anonymous',
        'picture'       => 'anonymous.jpg',
        'compatibility' => 0,
        'filters'       => array(false, false, false, false, false, false),
        'friends'       => false
    ),
    array(
        'id'            => 574,
        'name'          => 'Joe Bloggs',
        'picture'       => 'anonymous.jpg',
        'compatibility' => 80,
        'filters'       => array(true, true, true, false, true, null),
        'friends'       => false
    ),
    array(
        'id'            => 5436,
        'name'          => 'Anonymous',
        'picture'       => 'anonymous.jpg',
        'compatibility' => 100,
        'filters'       => array(true, true, true, true, true, true),
        'friends'       => false
    ),
    array(
        'id'            => 6747,
        'name'          => 'Anonymous',
        'picture'       => 'anonymous.jpg',
        'compatibility' => 20,
        'filters'       => array(false, false, false, false, true, false),
        'friends'       => false
    ),
    array(
        'id'            => 7568,
        'name'          => 'Anonymous',
        'picture'       => 'anonymous.jpg',
        'compatibility' => 60,
        'filters'       => array(false, false, true, true, true, true),
        'friends'       => false
    ),
    array(
        'id'            => 89745,
        'name'          => 'John Smith',
        'picture'       => 'anonymous.jpg',
        'compatibility' => 40,
        'filters'       => array(true, false, false, true, false, true),
        'friends'       => true
    )
);

// Sort the results array by the compatibility descending
usort($results, function ($a, $b) {
    return $a['compatibility'] < $b['compatibility'];
});

// Output the result list
echo '<ul class="box-list">';
// Script for showing/hiding details:
echo '<script>var showHideDetails=function(l){var d=l.parentNode.nextSibling,b=d.style.display===\'block\';d.style.display=b?\'none\':\'block\';l.className=\'link details details-\'+(b?\'down\':\'up\')}</script>';
foreach ($results as $person)
{
    echo '<div class="box"><div class="box-padding">';

    // Output the name and picture
    echo '<p class="text">';
    echo '<a href="profile?id='.$person['id'].'" class="link">';
    echo '<img src="../media/img/'.$person['picture'].'" class="profile-picture" alt="">';
    echo $person['name'];
    echo '</a>';
    // Allow the user to add/remove friends
    if ($person['friends'])
        echo ' <button class="input-button button2">Remove</button>';
    else
        echo ' <button class="input-button button2">Add</button>';
    echo '</p>';

    echo '<p class="text">';
    // Scale the compatibility to /255
    $comp255 = round($person['compatibility'] * 1.6);
    // Output the compatibility, colored
    $red = $comp255 > 80 ? (160 - $comp255) * 2: 160;
    $green = $comp255 < 80 ? $comp255 * 2 : 160;
    $blue = 0;
    echo '<span style="font-size:1.5em;line-height:0;color:rgba('.$red.','.$green.','.$blue.',1)">'.$person['compatibility'].'%</span>';

    // Output each filter
    echo ' <a class="link details details-down" onclick="showHideDetails(this)">Details</a>';
    echo '</p>';
    echo '<ul class="text" style="display: none">';
    foreach ($user['filters'] as $key => $value)
    {
        // If the user is ignoring this filter, ignore it
        if (isset($ignore[$key]) && $ignore[$key])
            continue;

        echo '<li style="list-style-image: url(\'/media/img/'.($person['filters'][$key] ? 'tick' : 'cross').'.png\')" class="small-text">';
        $userFilterString = is_bool($user['filters'][$key]) ? ($user['filters'][$key] ? 'True' : 'False') : $user['filters'][$key];
        echo $filters[$key]['name'].' ('.$userFilterString.')';
        echo '</li>';
    }
    echo '</ul></p>';

    echo '</div></div>';
}
echo '</ul>';

// Include the footer of the page
include '../../inc/html/footer.php';
// While the footer hasn't been made, I'll just do it here
?>
        <!-- Footer -->
        <div class="box">
            <ul class="box-padding footer">
                <li class="footer-item">Roomies &copy; 2014</li>
                <li class="footer-item"><a href="#" class="footer-link">About</a></li>
                <li class="footer-item"><a href="#" class="footer-link">Terms</a></li>
                <li class="footer-item"><a href="#" class="footer-link">Privacy</a></li>
                <li class="footer-item"><a href="#" class="footer-link">Cookies</a></li>
            </ul>
        </div>
    </div>
</body>
</html>