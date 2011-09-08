<?php
    require('connect.php');

    function nextUnflaggedResponse()
    {
        $ret = NULL;
        $query = mysql_query('SELECT url,response_id,raw_text FROM fb_response WHERE got_the_point IS NULL LIMIT 1');
        
        if(mysql_num_rows($query))
            $ret = mysql_fetch_assoc($query);
        
        return $ret;
    }
    
    if(count($_POST))
    {
        $got_point = NULL;

        if(isset($_POST['yes']))
            $got_point = 1;
        else if(isset($_POST['no']))
            $got_point = 0;
        
        if($got_point !== NULL)
        {
            $url         = $_POST['url'];
            $response_id = $_POST['response_id'];
            mysql_query('UPDATE fb_response SET got_the_point = '.(int)$got_point.' WHERE url = \''.mysql_real_escape_string($url).'\' AND response_id = '.(int)$response_id) or die(mysql_error());
        }
    }
?>
<html>
    <head>
        <title>Indicate whether respondents got the point of the exercise</title>
    </head>
    <body onload="document.getElementById('yes').focus();">
        <?php require('menu.php'); ?>
<?php if(!$next_response = nextUnflaggedResponse()) { ?>
        <h2>No more unflagged responses</h2>
<?php } else { ?>
        <h2>Response text:</h2>
        <h4>NB: If the original response contained more than one submission (due to incorrect formatting) then you will have split this up manually in the previous step. To this end you can ignore anything except the initial response below.</h4>
        <textarea name="original_text" style="width:800px;height:200px;"><?php echo htmlentities($next_response['raw_text']); ?></textarea>
        <form action="" method="post">
            <input type="hidden" name="url" value="<?php echo htmlentities($next_response['url']); ?>" />
            <input type="hidden" name="response_id" value="<?php echo htmlentities($next_response['response_id']); ?>" />
            <h2>Did they get the point?</h2>
            <table>
                <tr>
                    <td><input id="yes" type="submit" name="yes" value="Yes" /></td>
                    <td><input id="no" type="submit" name="no" value="No" /></td>
                </tr>
            </table>
        </form>
<?php } ?>
    </body>
</html>
