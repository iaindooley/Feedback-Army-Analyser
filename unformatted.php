<?php
    require('connect.php');

    function allQuestions()
    {
        return mysql_query('SELECT question_id,question_text FROM fb_question');
    }

    function nextUnformattedResponse()
    {
        $ret = NULL;
        $query = mysql_query('SELECT url,response_id,raw_text FROM fb_response LEFT OUTER JOIN fb_response_to_question
                              USING(url,response_id) WHERE fb_response_to_question.url IS NULL LIMIT 1') or die(mysql_error());
        
        if(mysql_num_rows($query))
            $ret = mysql_fetch_assoc($query);
        
        return $ret;
    }
    
    if(count($_POST))
    {
        $url         = $_POST['url'];
        $response_id = $_POST['response_id'];
        
        if(isset($_POST['new_response']) && $_POST['new_response'])
        {
            $fulltext = $_POST['original_text'];
            mysql_query('INSERT INTO fb_response(url,raw_text) VALUES(\''.mysql_real_escape_string($url).'\',\''.mysql_real_escape_string(trim($fulltext)).'\')');
        }
        
        foreach($_POST['questions'] as $question_id => $response)
            mysql_query('INSERT INTO fb_response_to_question(url,response_id,question_id,response_text) VALUES(\''.mysql_real_escape_string($url).'\','.(int)$response_id.','.(int)$question_id.',\''.mysql_real_escape_string($response).'\')');
    }
?>
<html>
    <head>
        <title>Correct incorrectly formatted responses</title>
    </head>
    <body>
        <?php require('menu.php'); ?>
<?php if(!$next_response = nextUnformattedResponse()) { ?>
        <h2>No more incorrectly formatted responses</h2>
<?php } else { ?>
        <h2>Response text:</h2>
        <h4>The format of this response could not be automatically determined. Cut and paste the text provided into the separate question boxes provided below</h4>
        <form action="" method="post">
            <input type="hidden" name="url" value="<?php echo htmlentities($next_response['url']); ?>" />
            <input type="hidden" name="response_id" value="<?php echo htmlentities($next_response['response_id']); ?>" />
            <table>
                <tr>
                    <textarea name="original_text" style="width:800px;height:200px;"><?php echo htmlentities($next_response['raw_text']); ?></textarea>
                </tr>
<?php     $questions = allQuestions(); while($row = mysql_fetch_assoc($questions)) { ?>
                <tr>
                    <td><?php echo htmlentities($row['question_text']); ?></td>
                </tr>
                <tr>
                    <td><textarea name="questions[<?php echo (int)$row['question_id']; ?>]" style="width:800px;height:50px;"></textarea></td>
                </tr>
<?php     } ?>
                <tr>
                    <td><input type="checkbox" name="new_response" value="1" /> Check this box to create a new response from text left over in the original text box up the top of the form</td>
                </tr>
                <tr><td colspan="2"><input type="submit" /></td></tr>
<?php } ?>
            </table>
        </form>
    </body>
</html>
