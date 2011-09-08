<?php
    require('connect.php');

    function automaticallyDetermineYesNo()
    {
        $responses = mysql_query('SELECT url,response_id,question_id,response_text,sentiment FROM fb_response_to_question WHERE is_affirmative IS NULL');
        
        while($row = mysql_fetch_assoc($responses))
        {
            $test = preg_replace('/^A\./','',$row['response_text']);
            $test = str_replace(array(',','.','Ans.'),' ',$test);
            $test = trim(preg_replace('/[^A-Za-z ]/','',$test));
            $is_affirmative = NULL;

            if(strpos(strtolower($test),'yes') === 0)
                $is_affirmative = 1;
            else if(strpos(strtolower($test),'no') === 0)
                $is_affirmative = 0;
            
            if($is_affirmative !== NULL)
            {
                $url = $row['url'];
                $response_id = $row['response_id'];
                $question_id = $row['question_id'];
                
                mysql_query('UPDATE fb_response_to_question SET is_affirmative = '.(int)$is_affirmative.' WHERE
                             url = \''.mysql_real_escape_string($url).'\' AND response_id = '.(int)$response_id.'
                             AND question_id = '.(int)$question_id) or die(mysql_error());
                             
            }
        }
    }

    function questionResponsesWithNullYesNo()
    {
        return mysql_query('SELECT url,response_id,question_id,response_text,is_affirmative FROM fb_response_to_question ORDER BY is_affirmative');
    }
    
    automaticallyDetermineYesNo();

    if(count($_POST))
    {
        foreach($_POST['is_affirmative'] as $url => $responses)
        {
            foreach($responses as $response_id => $questions)
            {
                foreach($questions as $question_id => $is_affirmative)
                {
                    if($is_affirmative !== '')
                        mysql_query('UPDATE fb_response_to_question SET is_affirmative = '.(int)mysql_real_escape_string($is_affirmative).' WHERE url = \''.mysql_real_escape_string($url).'\' AND response_id = '.(int)$response_id.' AND question_id = '.(int)$question_id) or die(mysql_error());
                }
            }
        }
    }
?>
<html>
    <head>
        <title>Set the sentiment of responses</title>
    </head>
    <body>
        <?php require('menu.php'); ?>
<?php $alt = 1; $query = questionResponsesWithNullYesNo(); ?>
<?php if(!mysql_num_rows($query)) { ?>
        <h2>No unconfirmed questions</h2>
<?php } else { ?>
        <form action="" method="post">
            <table>
<?php     while($resp = mysql_fetch_assoc($query)) { ?>
                <tr <?php echo (!($alt % 2)) ? 'style="background:#dfdfdf;"':''; ?>>
                    <td><?php echo $resp['response_text']; ?>:</td>
                    <td>
                        <select name="is_affirmative[<?php echo $resp['url']; ?>][<?php echo $resp['response_id']; ?>][<?php echo $resp['question_id']; ?>]">
                            <option value="">Choose ... </option>
                            <option value="0"<?php echo ($resp['is_affirmative'] === '0') ? ' selected':''; ?>>Not Affirmative</option>
                            <option value="1"<?php echo ($resp['is_affirmative'] === '1') ? ' selected':''; ?>>Affirmative</option>
                        </select>
                    </td>
                </tr>
<?php $alt++; } ?>
<?php } ?>
                <tr><td colspan="2"><input type="submit" /></td></tr>
            </table>
        </form>
    </body>
</html>
