<?php
// Initialize session
session_start();

if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = ['right' => 0, 'wrong' => 0];
}
if (!isset($_SESSION['quiz_settings'])) {
    $_SESSION['quiz_settings'] = [
        'operator' => 'multiply', 
        'num_items' => 4,
        'max_item' => 2, 
        'level' => '1-10',
        'custom_level_start' => 1,
        'custom_level_end' => 10,
        'answer_range' => 5 
    ];
}
if (!isset($_SESSION['quiz_started'])) {
    $_SESSION['quiz_started'] = false;
}
if (!isset($_SESSION['show_settings'])) {
    $_SESSION['show_settings'] = false;
}
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0; 
}

// Handle form submissions
if (isset($_POST['close_quiz'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['start_quiz'])) {
    $_SESSION['quiz_started'] = true;
    $_SESSION['score'] = ['right' => 0, 'wrong' => 0];
    $_SESSION['current_question'] = 1; 
}

if (isset($_POST['toggle_settings'])) {
    $_SESSION['show_settings'] = !$_SESSION['show_settings'];
}

if (isset($_POST['update_settings'])) {
    $_SESSION['quiz_settings']['max_item'] = (int)$_POST['max_item']; 
    $_SESSION['quiz_settings']['level'] = $_POST['level'];
    $_SESSION['quiz_settings']['answer_range'] = (int)$_POST['answer_range'];
    $_SESSION['quiz_settings']['operator'] = $_POST['operator']; // Update the operator

    if ($_POST['level'] === 'custom') {
        $_SESSION['quiz_settings']['custom_level_start'] = (int)$_POST['custom_level_start'];
        $_SESSION['quiz_settings']['custom_level_end'] = (int)$_POST['custom_level_end'];
    }

    $_SESSION['show_settings'] = false; // Close settings after update
}

// Check if quiz is started and answer is submitted
if ($_SESSION['quiz_started'] && isset($_POST['answer'])) {
    $num1 = $_POST['num1'];
    $num2 = $_POST['num2'];
    $operator = $_SESSION['quiz_settings']['operator'];

    // Calculate correct answer
    switch ($operator) {
        case 'add':
            $correct_answer = $num1 + $num2;
            break;
        case 'subtract':
            $correct_answer = $num1 - $num2;
            break;
        case 'multiply':
            $correct_answer = $num1 * $num2;
            break;
    }

    // Check if answer is correct
    if ((int)$_POST['answer'] === $correct_answer) {
        $_SESSION['score']['right']++;
        $message = "Correct!";
    } else {
        $_SESSION['score']['wrong']++;
        $wrongs = "Wrong! Correct answer was $correct_answer.";
    }

    // Increment question number
    if ($_SESSION['current_question'] < $_SESSION['quiz_settings']['max_item']) {
        $_SESSION['current_question']++;
    } else {
        // Stop quiz and show final score
        $_SESSION['quiz_started'] = false;
        $message = "Quiz completed! Final score: Correct: {$_SESSION['score']['right']}, Wrong: {$_SESSION['score']['wrong']}";
    }
}
?>