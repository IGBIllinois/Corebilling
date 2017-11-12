<?php
$adminType = 1;
$selectedArticle = 0;

if(isset($_GET['edit']))
{
		$selectedArticle = $_GET['edit'];
}

if(isset($_GET['delete']) && $_SESSION['usertype']==$adminType)
{
	$queryDeleteArticle = "DELETE FROM articles WHERE ID=".$_GET['delete'];
	$sqlDataBase->nonSelectQuery($queryDeleteArticle);
}

if(isset($_POST['applyEdit']) && $_SESSION['usertype']==$adminType)
{
	$title = mysql_real_escape_string($_POST['title']);
        $bodyText = mysql_real_escape_string($_POST['text']);
	$articleId = mysql_real_escape_string($_POST['editArticleId']);
	$queryUpdateArticle = "UPDATE articles SET title=\"".$title."\", text=\"".$bodyText."\" WHERE ID=".$articleId;
	$sqlDataBase->nonSelectQuery($queryUpdateArticle);
}

if(isset($_POST['createNew']) && $_SESSION['usertype']==$adminType)
{
	$title = mysql_real_escape_string($_POST['title']);
	$bodyText = mysql_real_escape_string($_POST['text']);
	$queryInsertNewArticle = "INSERT INTO articles (time,userid,title,text,user,status)VALUES(NOW(),".$_SESSION['userid'].",\"".$title."\",\"".$bodyText."\",\"\",0)";
	$sqlDataBase->insertQuery($queryInsertNewArticle);
}

?>


<center><h4>Latest News</h4></center><br>
<form name="articlesForms" action="index.php?subm=1" method="post">
<?php
if(isset($_SESSION['usertype']))
{
	if($_SESSION['usertype']==$adminType)
	{
		
		echo "<div id=\"news\">";
		echo "<div id=\"newstitle\"><input type=\"text\" size=\"60\" value=\"\" name=\"title\"></div>
                        <div id=\"newsbody\"><textarea name=\"text\" style=\"width:100%; height:100px;\"></textarea></div>
			<input type=\"submit\" class=\"grey\" name=\"createNew\" value=\"Create New Article\"><br><br>";
		echo "</div>";
	}
}

$queryNewsArticles = "SELECT a.ID,a.time,a.userid,a.text,a.title,a.status, u.first, u.last,u.email FROM articles a, users u WHERE u.ID = a.userid ORDER BY a.time DESC";
$newsArticles = $sqlDataBase->query($queryNewsArticles);
foreach($newsArticles as $id=>$newsArticle)
{
	echo "<div id=\"news\">";

	if($selectedArticle==$newsArticle['ID'] && $_SESSION['usertype']==$adminType)
	{
		echo "<div id=\"newstitle\"><input type=\"text\" size=\"60\" value=\"".$newsArticle['title']."\" name=\"title\"><input type=\"submit\" name=\"applyEdit\" value=\"Apply\" class=\"grey\"><input type=\"hidden\" name=\"editArticleId\" value=".$newsArticle['ID']."></div>
                        <div id=\"newsbody\"><textarea name=\"text\" style=\"width:100%; height:100px;\">".$newsArticle['text']."</textarea></div>";
	}
	else
	{
		echo "<div id=\"newstitle\">".$newsArticle['title']."</div>
			<div id=\"newsbody\">".$newsArticle['text']."</div>";
	}
	
	echo "<div id=\"newsfooter\">";

	if(isset($_SESSION['usertype']))
	{
        	if($_SESSION['usertype']==$adminType)
		{
			echo "<a href=\"index.php?subm=1&edit=".$newsArticle['ID']."\">Edit</a> | <a href=\"index.php?subm=1&delete=".$newsArticle['ID']."\">Delete</a> | ";
		}
	}

	echo $newsArticle['first']." ".$newsArticle['last']." | ".$newsArticle['email']." | ".$newsArticle['time']."</div>
		</div>";
}
?>
</form>
