<?php
/**
 * survey_test_final.php is a page to demonstrate the proof of concept of the 
 * initial SurveySez objects.
 *
 * Objects in this version are the Survey, Question & Answer objects
 * 
 * @package SurveySez
 * @author Bill Newman <williamnewman@gmail.com>
 * @version 2.1 2011/10/25
 * @link http://www.billnsara.com/advdb/  
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License ("OSL") v. 3.0
 * @see config_inc.php  
 * @todo none
 */
 
require '../inc_0700/config_inc.php'; #provides configuration, pathing, error handling, db credentials
$config->metaRobots = 'no index, no follow';#never index survey pages
/*
$config->metaDescription = ''; #Fills <meta> tags.
$config->metaKeywords = '';
$config->metaRobots = '';
$config->loadhead = ''; #load page specific JS
$config->banner = ''; #goes inside header
$config->copyright = ''; #goes inside footer
$config->sidebar1 = ''; #goes inside left side of page
$config->sidebar2 = ''; #goes inside right side of page
$config->nav1["page.php"] = "New Page!"; #add a new page to end of nav1 (viewable this page only)!!
$config->nav1 = array("page.php"=>"New Page!") + $config->nav1; #add a new page to beginning of nav1 (viewable this page only)!!
*/
 

# check variable of item passed in - if invalid data, forcibly redirect back to demo_list.php page
if(isset($_GET['id']) && (int)$_GET['id'] > 0){#proper data must be on querystring
	 $myID = (int)$_GET['id']; #Convert to integer, will equate to zero if fails
}else{
	myRedirect(VIRTUAL_PATH . "surveys/survey_list.php");
}

$mySurvey = new Survey($myID);
if($mySurvey->isValid)
{
	$config->titleTag = "'" . $mySurvey->Title . "' Survey!";
}else{
	$config->titleTag = smartTitle(); //use constant 
}
#END CONFIG AREA ---------------------------------------------------------- 

get_header(); #defaults to theme header or header_inc.php
?>
<h3><?=THIS_PAGE;?></h3>

<?php

if($mySurvey->isValid)
{ #check to see if we have a valid SurveyID
	echo $mySurvey->SurveyID . "<br />";
	echo $mySurvey->Title . "<br />";
	echo $mySurvey->Description . "<br />";
	$mySurvey->showQuestions();
	responseList($myID);
}else{
	echo "Sorry, no such survey!";	
}

get_footer(); #defaults to theme footer or footer_inc.php

function responseList($myID)
{//will create a list of responses for this survey
	
	# SQL statement
	$sql = "select * from sp14_responses where SurveyID=$myID";
	
	#reference images for pager
	$prev = '<img src="' . VIRTUAL_PATH . 'images/arrow_prev.gif" border="0" />';
	$next = '<img src="' . VIRTUAL_PATH . 'images/arrow_next.gif" border="0" />';
	
	# Create instance of new 'pager' class
	$myPager = new Pager(10,'',$prev,$next,'');
	$sql = $myPager->loadSQL($sql);  #load SQL, add offset
	
	# connection comes first in mysqli (improved) function
	$result = mysqli_query(IDB::conn(),$sql) or die(trigger_error(mysqli_error(IDB::conn()), E_USER_ERROR));
	
	if(mysqli_num_rows($result) > 0)
	{#records exist - process
		if($myPager->showTotal()==1){$itemz = "response";}else{$itemz = "responses";}  //deal with plural
	    echo '<div align="center">We have ' . $myPager->showTotal() . ' ' . $itemz . '!</div>';
		while($row = mysqli_fetch_assoc($result))
		{# process each row
		
		 # Identify the Date Created, Title, Description and Creator's Name (Admin's First & Last Name) of each survey
	    /* SQL statement
		$sql = 
		"
		select CONCAT(a.FirstName, ' ', a.LastName) AdminName, s.SurveyID, s.Title, s.Description, 
		date_format(s.DateAdded, '%W %D %M %Y %H:%i') 'DateAdded' from "
		. PREFIX . "surveys s, " . PREFIX . "Admin a where s.AdminID=a.AdminID order by s.DateAdded desc
		";
	    */
		     echo '<div align="center"><a href="' . VIRTUAL_PATH . 'surveys/response_view.php?id=' . (int)$row['ResponseID'] . '">' . dbOut($row['DateAdded']) . '</a>';
	         echo '</div>';
		}
		echo $myPager->showNAV(); # show paging nav, only if enough records	 
	}else{#no records
	    echo "<div align=center>There are currently no responses.</div>";	
	}
	@mysqli_free_result($result);
	

}