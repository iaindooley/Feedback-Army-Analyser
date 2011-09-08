<?php
    require('connect.php');
    mysql_query('TRUNCATE fb_url');
    mysql_query('TRUNCATE fb_response');
    mysql_query('TRUNCATE fb_question');
    mysql_query('TRUNCATE fb_response_to_question');

    /***** DEFINE YOUR QUESTIONS HERE ****/
    $questions = array(
'Does the copy on this site make you want to do business with us?',
'If you wanted to get a website online for your business does the copy on this site make you feel comfortable that we\'d be able to deliver?',
'Do you think that the information on this site is organised such that you are able to find out what you need in order to make a purchasing decision?',
'Once you have made a purchasing decision is it clear how to act and what will happen when you do?',
'Have you ever built a website before yourself?',
'Have you ever paid someone else to build a website on your behalf?',
);
    /***** END QUESTION DEFINITION ****/

    
    /***** CONFIGURE YOUR DIFFERENT URLS AND FILE NAMES HERE ****/

    $voices = array('voice1.mockups.decalcms.com' => array(),
                    'voice2.mockups.decalcms.com' => array(),
                    'voice3.mockups.decalcms.com' => array(),
                   );

    importFile('fb_army/voice1.txt',$voices['voice1.mockups.decalcms.com']);
    importFile('fb_army/voice2.txt',$voices['voice2.mockups.decalcms.com']);
    importFile('fb_army/voice3.txt',$voices['voice3.mockups.decalcms.com']);

    /***** END CONFIGURATION OF URLS AND FILE NAMES ****/

    define('NUMBER_OF_QUESTIONS',count($questions));

    function importFile($path,&$voice)
    {
        $lines = array_filter(file($path,FILE_IGNORE_NEW_LINES));
        $cur_review = '';
        
        foreach($lines as $line)
        {
            if(strpos($line,'Review time:') !== FALSE)
            {
                $voice[] = $cur_review;
                $cur_review = '';
            }
            
            else
                $cur_review .= $line.PHP_EOL;
        }
    }

    foreach($voices as $url => $reviews)
    {
        createUrl($url);
        
        foreach($reviews as $review)
        {
            $response_id = createResponse($url,$review);
            $split = array_filter(explode(PHP_EOL,$review));
            array_pop($split);
            
            if(count($split) == NUMBER_OF_QUESTIONS)
            {
                foreach($split as $key => $response)
                    addResponseToQuestion($url,$response_id,$key,$response);
            }
        }
    }

    function addResponseToQuestion($url,$response_id,$question_number,$response)
    {
        global $questions;
        $check = mysql_query('SELECT question_id FROM fb_question WHERE question_text = \''.mysql_real_escape_string($questions[$question_number]).'\'');
        
        if(!mysql_num_rows($check))
        {
            mysql_query('INSERT INTO fb_question(question_text) VALUES(\''.mysql_real_escape_string($questions[$question_number]).'\')');
            $question_id = mysql_insert_id();
        }
        
        else
            $question_id = mysql_result($check,0,'question_id');
        
        mysql_query('INSERT INTO fb_response_to_question(url,response_id,question_id,response_text) VALUES(\''.mysql_real_escape_string($url).'\','.(int)$response_id.','.(int)$question_id.',\''.mysql_real_escape_string($response).'\')');
    }

    function createResponse($url,$fulltext)
    {
        mysql_query('INSERT INTO fb_response(url,raw_text) VALUES(\''.mysql_real_escape_string($url).'\',\''.mysql_real_escape_string(trim($fulltext)).'\')');
        return mysql_insert_id();
    }

    function createUrl($url)
    {
        mysql_query('REPLACE INTO fb_url VALUES(\''.mysql_real_escape_string($url).'\')');
    }
