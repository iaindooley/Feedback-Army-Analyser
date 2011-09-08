SELECT 
    question_text,COUNT(DISTINCT t1.url,t1.response_id) AS num_respondents

FROM fb_response_to_question AS t1
INNER JOIN fb_question USING(question_id)
INNER JOIN fb_response_to_question AS t2
ON t1.url = t2.url AND t1.response_id = t2.response_id AND t2.sentiment = 'POSITIVE'

WHERE t1.is_affirmative = 0 
GROUP BY t1.question_id
;
