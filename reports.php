<?php
/*
DROP TABLE IF EXISTS `fb_url`;
CREATE TABLE `fb_url` (
  `url` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY  (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `fb_response`;
CREATE TABLE `fb_response` (
  `url` varchar(200) NOT NULL DEFAULT '',
  `response_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_text` text DEFAULT NULL,
  `got_the_point` tinyint(1) DEFAULT NULL,
  PRIMARY KEY  (`url`,`response_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `fb_question`;
CREATE TABLE `fb_question` (
  `question_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_text` varchar(200) DEFAULT NULL,
  PRIMARY KEY  (`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `fb_response_to_question`;
CREATE TABLE `fb_response_to_question` (
  `url` varchar(200) NOT NULL DEFAULT '',
  `response_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `question_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `response_text` text DEFAULT NULL,
  `is_affirmative` tinyint(1) DEFAULT NULL,
  `sentiment` enum('NEGATIVE','NEUTRAL','POSITIVE') DEFAULT NULL,
  PRIMARY KEY  (`url`,`response_id`,`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
*/
    require('connect.php');

    function affirmativePercentages()
    {
        $query = mysql_query('SELECT url,question_text,
                            SUM(IF((is_affirmative = 1),1,0)) AS num_affirmative,
                            COUNT(response_id) AS total_responses
                            FROM fb_question INNER JOIN fb_response_to_question USING(question_id)
                            GROUP BY url,question_id ORDER BY url,question_id') or die(mysql_error());
        $ret = array();
        
        while($row = mysql_fetch_assoc($query))
        {
            if(!isset($ret[$row['url']]))
                $ret[$row['url']] = array();
            
            $ret[$row['url']][] = sprintf('%.2f',($row['num_affirmative']*100)/$row['total_responses']);
        }
        
        return $ret;
    }
    
    function allQuestions()
    {
        return mysql_query('SELECT question_id,question_text FROM fb_question ORDER BY question_id');
    }
    
    function urlSentiments()
    {
        $query = mysql_query('SELECT url,
                              SUM(IF((sentiment = \'NEGATIVE\'),1,0)) AS num_negative,
                              SUM(IF((sentiment = \'NEUTRAL\'),1,0)) AS num_neutral,
                              SUM(IF((sentiment = \'POSITIVE\'),1,0)) AS num_positive
                              FROM fb_response_to_question GROUP BY url') or die(mysql_error());
        return $query;
    }
?>
<html>
    <head>
        <title>View reports</title>
    </head>
    <body>
        <?php require('menu.php'); ?>
        <h2>The long awaited feedback</h2>
        <h3>Questions by number:</h3>
        <ol>
<?php $questions = allQuestions(); while($row = mysql_fetch_assoc($questions)) { ?>
            <li><?php echo htmlentities($row['question_text']); ?></li>
<?php } ?>
        </ol>
        <h3>Percentage of affirmative answers per question for each URL:</h3>
        <table cellpadding="3" border="1">
            <tr>
                <td>URL</td>
<?php $questions = allQuestions(); while($row = mysql_fetch_assoc($questions)) { ?>
                <td><?php echo htmlentities($row['question_id']); ?>
<?php } ?>
            </tr>
<?php foreach(affirmativePercentages() as $url => $percentages) { ?>
            <tr>
                <td><?php echo htmlentities($url); ?></td>
<?php     foreach($percentages as $perc) { ?>
                <td><?php echo $perc; ?>%</td>
<?php     } ?>
            </tr>
<?php }     ?>
        </table>
        <h3>Number of responses to questions by sentiment for each URL:</h3>
        <table cellpadding="3" border="1">
            <tr>
                <td>URL</td>
                <td>Negative</td>
                <td>Neutral</td>
                <td>Positive</td>
            </tr>
<?php $url_sentiments = urlSentiments(); while($row = mysql_fetch_assoc($url_sentiments)) { ?>
            <tr>
                <td><?php echo htmlentities($row['url']); ?></td>
                <td><?php echo htmlentities($row['num_negative']); ?></td>
                <td><?php echo htmlentities($row['num_neutral']); ?></td>
                <td><?php echo htmlentities($row['num_positive']); ?></td>
            </tr>
<?php } ?>
    </body>
</html>
