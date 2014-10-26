<?php
require("config.php");
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$q = "SELECT * FROM `warparse_logs` ORDER BY `id` DESC LIMIT 100;";
$result = $conn->query($q);


?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="refresh" content="30" />
<title>Urban Terror Warbot</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<div id="all">
	<h1>Urban Terror Warbot</h1>
	<ul id="list">
		<?php
		/*
		Types:
		1. CW
		2. PCW
		3. Ringer
		4. Recruit
		5. Message
		*/
		$typeNames = array("", "cw", "pcw", "ringer", "recruit", "msg");

		$tagMap = array(
			"ts" => "Team Survivor",
			"ctf" => "Capture the Flag",
			"bomb" => "Bomb Mode",
			"hs" => "Have Server",
			"ns" => "Need Server"
		);
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$type = $row["type"];

				echo("<li><h2>" . $typeNames[$type] . "</h2>");
				if ($type == 5) {
					$info = $row["info"];
					$urlRegex = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
					if (preg_match($urlRegex, $info, $url))
						$info = preg_replace($urlRegex, '<a href="'.$url[0].'" rel="nofollow">'.$url[0].'</a>', $info);
					echo("<div class=\"info\">$info</div>");
				} else {
					/*
					Skill:
					0. Unknown
					1. Low
					2. Medium
					3. High
					*/
					$skill = 0;
					$skillStrings = array("<span class=\"skill-unknown\">Unknown</span>", "<span class=\"skill-low\">Low</span>", "<span class=\"skill-medium\">Medium</span>", "<span class=\"skill-high\">High</span>");
					$num = $row["num"];
					if ($type == 1 || $type == 2) {
						$tags = "<div class=\"tag\">$num vs $num</div>";
					} else if ($type == 3) {
						$tags = "<div class=\"tag\">Need: $num</div>";
					}
					
					if ($row["info"] !== NULL) {
						$rawTags = explode(" ", $row["info"]);
						
						foreach($rawTags as $tag) {
							if ($tag === "")
								continue;

							if ($tag == "high") {
								$skill = 3;
								continue;
							} else if ($tag == "med" || $tag == "medium" || $tag == "mid") {
								$skill = 2;
								continue;
							} else if ($tag == "low") {
								$skill = 1;
								continue;
							}

							$t = strtolower($tag);
							if (array_key_exists($t, $tagMap))
								$t = $tagMap[$t];
							else
								$t = ucfirst($t);

							$tags .= "<div class=\"tag\">" . $t . "</div> ";
						}
					}
					echo("<div class=\"tags\">$tags</div>");
					echo("<div class=\"skill\">Skill: " . $skillStrings[$skill] . "</div>");
				}

				$user = $row["user"];
				$channel = $row["channel"];
				$network = $row["network"];
				echo("<div class=\"contact\"><span class=\"user\">$user</span> in <span class=\"channel\">#$channel</span> on <span class=\"network\">$network</span></div>");

				$time = $row["time"];
				$tdelta = time() - $time;
				$minutes = $tdelta / 60;
				if ($minutes > 59) {
					$hours = $minutes / 60;
					if ($hours > 23) {
						$days = $hours / 24;
						$timestring = strval((int)$days) . " day" . ((int)$days == 1 ? "" : "s") . " ago";
					} else {
						$timestring = strval((int)$hours) . " hour" . ((int)$hours == 1 ? "" : "s") . " ago";
					}
				} else {
					$timestring = strval((int)$minutes) . " minute" . ((int)$minutes == 1 ? "" : "s") . " ago";
				}
				echo("<div class=\"time\">$timestring</div>");
			}
		}
		?>
<!-- 		<li>
			<h2>pcw</h2>
			<div class="tags"><span class="tag">5 vs 5</span> <span class="tag">Team Survivor</span></div>
			<div class="skill">Skill: <span class="skill-high">High</span></div>
			<div class="contact"><span class="user">clearskies</span> in <span class="channel">#skiesclear</span> on <span class="network">Quakenet</span></div>
			<div class="time">0 minutes ago</div>
		</li> -->
	</ul>
</div>
</body>
</html>