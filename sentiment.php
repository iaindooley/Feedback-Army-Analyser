<?php
    require('connect.php');

    function questionResponses()
    {
        return mysql_query('SELECT url,response_id,question_id,response_text,sentiment FROM fb_response_to_question');
    }
    
    if(count($_POST))
    {
        foreach($_POST['sentiments'] as $url => $responses)
            foreach($responses as $response_id => $questions)
                foreach($questions as $question_id => $sentiment)
                    mysql_query('UPDATE fb_response_to_question SET sentiment = \''.mysql_real_escape_string($sentiment).'\' WHERE url = \''.mysql_real_escape_string($url).'\' AND response_id = '.(int)$response_id.' AND question_id = '.(int)$question_id) or die(mysql_error());
    }
?>
<html>
    <head>
        <title>Set the sentiment of responses</title>
    </head>
    <body>
        <?php require('menu.php'); ?>
        <form action="" method="post">
            <table>
<?php $alt = 1; $query = questionResponses(); while($resp = mysql_fetch_assoc($query)) { ?>
                <tr <?php echo (!($alt % 2)) ? 'style="background:#dfdfdf;"':''; ?>>
                    <td><?php echo $resp['response_text']; ?>:</td>
                    <td>
                        <select name="sentiments[<?php echo $resp['url']; ?>][<?php echo $resp['response_id']; ?>][<?php echo $resp['question_id']; ?>]">
                            <option value="">Choose ... </option>
                            <option value="NEGATIVE"<?php echo ($resp['sentiment'] == 'NEGATIVE') ? ' selected':''; ?>>NEGATIVE</option>
                            <option value="NEUTRAL"<?php echo ($resp['sentiment'] == 'NEUTRAL') ? ' selected':''; ?>>NEUTRAL</option>
                            <option value="POSITIVE"<?php echo ($resp['sentiment'] == 'POSITIVE') ? ' selected':''; ?>>POSITIVE</option>
                        </select>
                    </td>
                </tr>
<?php $alt++; } ?>
                <tr><td colspan="2"><input type="submit" /></td></tr>
            </table>
        </form>
    </body>
</html>
