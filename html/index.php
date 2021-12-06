<?php
require_once 'includes/header.inc.php';

$user = new User($db,$ldap);
$articlesList = Article::getAllArticles($db,settings::get_news_age());

$articles_html = "";
if (count($articlesList)) {
	foreach ($articlesList as $articleInfo) { 
		$formattedText = str_replace("\n", "<br/>", $articleInfo['text']);
		$articles_html .= "<div class='panel panel-default'>";
		$articles_html .= "<div class='panel-heading'><h4><b>" . $articleInfo['title'] . "</b></h4></div>";
		$articles_html .= "<div class='panel-body'>" . $formattedText . "</div>";
		$articles_html .= "<div class='panel-footer'>";
		if ($login_user->isAdmin()) {
			$articles_html .= "<a href=\"edit_news.php?edit=" . $articleInfo['id'] . "\">Edit</a> | <a href=\"edit_news.php?delete=" . $articleInfo['id'] . "\">Delete</a> | ";
		}
		$user->load($articleInfo['user_id']);
		$articles_html .= "<small>".$user->getFirstName() . " " . $user->getLastName() . " | " . $user->getEmail() . " | " . date("Y-m-d",strtotime($articleInfo['created'])) . "</small>";
		$articles_html .= "</div></div><div>";
	}
}
else {
	$articles_html = "No Current News";
}
?>
<div>
<?php if($login_user->isAdmin()) {
	echo "<div><a href='edit_news.php' class='btn btn-xs btn-primary pull-right'><span class='glyphicon glyphicon-plus'></span> Add News</a></div>";
}
?>
<h3>Latest News</h3>
</div>
<?php

echo $articles_html;
	
require_once 'includes/footer.inc.php';
?>

