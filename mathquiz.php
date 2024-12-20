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

if ($_SESSION['quiz_started'] && $_SESSION['current_question'] <= $_SESSION['quiz_settings']['max_item']) {
    $range = [1, 10]; 

    // Set range based on level
    if ($_SESSION['quiz_settings']['level'] === '1-10') {
        $range = [1, 10];
    } elseif ($_SESSION['quiz_settings']['level'] === '11-100') {
        $range = [11, 100];
    } elseif ($_SESSION['quiz_settings']['level'] === 'custom') {
        $range = [$_SESSION['quiz_settings']['custom_level_start'], $_SESSION['quiz_settings']['custom_level_end']];
    }

    $num1 = rand($range[0], $range[1]);
    $num2 = rand($range[0], $range[1]);

    
    $operator_symbol = '';
    switch ($_SESSION['quiz_settings']['operator']) {
        case 'add':
            $operator_symbol = '+';
            break;
        case 'subtract':
            $operator_symbol = '-';
            break;
        case 'multiply':
            $operator_symbol = '×';
            break;
    }

    $correct_answer = 0;
    switch ($_SESSION['quiz_settings']['operator']) {
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

    $range_offset = isset($_SESSION['quiz_settings']['answer_range']) ? $_SESSION['quiz_settings']['answer_range'] : 5; 

    
    $choices = [$correct_answer];

    while (count($choices) < 4) { 
        $choice = rand($correct_answer - $range_offset, $correct_answer + $range_offset);

        if (!in_array($choice, $choices)) {
            $choices[] = $choice;
        }
    }
    shuffle($choices);
}else {
    $num1 = 0;
    $num2 = 0;
    $operator_symbol = '+';
    $correct_answer = 0;
    $choices = [0, 1, 2, 3];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Math Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Light grey background */
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff; /* White background */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .header-title {
            font-size: 2.2em;
            font-weight: bold;
            text-align: center;
            color: #4a90e2;
        }
        .question {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .score-board {
            display: flex;
            justify-content: space-between;
            font-size: 1.2em;
            margin-bottom: 20px;
            color: #6c757d;
        }
        .btn {
            font-size: 1.1em;
            padding: 10px 15px;
        }
        .settings-section {
            background-color: #e9ecef; /* Light grey for settings */
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .settings-section h3 {
            color: #495057;
        }
        .message {
            font-size: 1.2em;
            text-align: center;
            margin-top: 10px;
        }
        .correct-message {
            color: #28a745; /* Green for correct */
        }
        .wrong-message {
            color: #dc3545; /* Red for wrong */
        }
    </style>
</head>
<body>
    < class="container">
        <h1 class="header-title">Simple Math Game</h1>

        <?php if ($_SESSION['quiz_started']): ?>
            <!-- Quiz In Progress -->
            <div class="question">
                <?php echo "$num1 $operator_symbol $num2 = ?"; ?>
            </div>
            <form method="post" class="text-center">
                <input type="hidden" name="num1" value="<?= $num1 ?>">
                <input type="hidden" name="num2" value="<?= $num2 ?>">
                <?php foreach ($choices as $choice): ?>
                    <button type="submit" name="answer" value="<?= $choice ?>" class="btn btn-primary m-2">
                        <?= $choice ?>
                    </button>
                <?php endforeach; ?>
            </form>
        <?php else: ?>
            <!-- Quiz Completed or Main Menu -->
            <div class="text-center">
                <h3 class="message">Quiz Completed!</h3>
                <p>Your Final Score:</p>
                <div class="score-board">
                    <p>✅ Correct: <strong><?= $_SESSION['score']['right'] ?></strong></p>
                    <p>❌ Wrong: <strong><?= $_SESSION['score']['wrong'] ?></strong></p>
                </div>
                <form method="post" class="d-flex justify-content-center">
                    <button type="submit" name="start_quiz" class="btn btn-success m-2">
                        <i class="bi bi-play-fill"></i> Start Quiz
                    </button>
                    <button type="submit" name="close_quiz" class="btn btn-danger m-2">
                        <i class="bi bi-x-circle"></i> Close
                    </button>
                    <button type="submit" name="toggle_settings" class="btn btn-secondary m-2">
                        <i class="bi bi-gear-fill"></i> <?= $_SESSION['show_settings'] ? 'Close Settings' : 'Settings >>' ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['show_settings']): ?>
            <div class="settings-section">
                <h3>Settings</h3>
                <form method="post">
                    <div class="mb-3">
                        <label>Level:</label><br>
                        <input type="radio" name="level" value="1-10" <?= $_SESSION['quiz_settings']['level'] === '1-10' ? 'checked' : '' ?>> 1-10<br>
                        <input type="radio" name="level" value="11-100" <?= $_SESSION['quiz_settings']['level'] === '11-100' ? 'checked' : '' ?>> 11-100<br>
                        <input type="radio" name="level" value="custom" <?= $_SESSION['quiz_settings']['level'] === 'custom' ? 'checked' : '' ?>> Custom Range<br>
                    </div>

                    <?php if ($_SESSION['quiz_settings']['level'] === 'custom'): ?>
                        <div class="mb-3">
                            <label>Start: <input type="number" name="custom_level_start" value="<?= $_SESSION['quiz_settings']['custom_level_start'] ?>" class="form-control"></label><br>
                            <label>End: <input type="number" name="custom_level_end" value="<?= $_SESSION['quiz_settings']['custom_level_end'] ?>" class="form-control"></label><br>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label>Max Questions:</label><br>
                        <input type="number" name="max_item" value="<?= $_SESSION['quiz_settings']['max_item'] ?>" class="form-control"><br>
                    </div>

                    <div class="mb-3">
                        <label>Answer Range:</label><br>
                        <input type="number" name="answer_range" value="<?= $_SESSION['quiz_settings']['answer_range'] ?>" min="1" class="form-control"><br>
                    </div>

                    <div class="mb-3">
                        <label>Operator:</label><br>
                        <input type="radio" name="operator" value="add" <?= $_SESSION['quiz_settings']['operator'] === 'add' ? 'checked' : '' ?>> Addition (+)<br>
                        <input type="radio" name="operator" value="subtract" <?= $_SESSION['quiz_settings']['operator'] === 'subtract' ? 'checked' : '' ?>> Subtraction (-)<br>
                        <input type="radio" name="operator" value="multiply" <?= $_SESSION['quiz_settings']['operator'] === 'multiply' ? 'checked' : '' ?>> Multiplication (×)<br>
                    </div>

                    <div class="text-center">
                        <button type="submit" name="update_settings" class="btn btn-primary">Update Settings</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
