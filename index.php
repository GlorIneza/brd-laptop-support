<?php
require 'db.php';
require 'sms.php'; 

$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"];

$parts = explode("*", $text);
$level = count($parts);

$student = null;
$stmt = $mysqli->prepare("SELECT * FROM students WHERE phone = ?");
$stmt->bind_param("s", $phoneNumber);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
}

if ($text == "") {
    if ($student) {
        echo "CON Welcome to BRD Laptop Support\n";
        echo "1. Report Issue\n";
        echo "2. Check Status";
    } else {
        echo "CON Welcome to BRD Laptop Support\n";
        echo "1. Register";
    }
}

else if (!$student && $parts[0] == "1") {
    if ($level == 1) {
        echo "CON Enter your full name:";
    } else if ($level == 2) {
        echo "CON Enter your level of study (6 or 7):";
    } else if ($level == 3) {
        echo "CON Enter your department:";
    } else if ($level == 4) {
        echo "CON Enter your RP college name:";
    } else if ($level == 5) {
        $name = $parts[1];
        $levelStudy = $parts[2];
        $dept = $parts[3];
        $college = $parts[4];

        $stmt = $mysqli->prepare("INSERT INTO students (phone, name, level, department, college)
                                  VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $phoneNumber, $name, $levelStudy, $dept, $college);
        $stmt->execute();

        sendSMS($phoneNumber, "You, $name, are successfully registered.");

        echo "END Registration successful. You will now be able to report issues.";
    }
}

else if ($student) {
    if ($parts[0] == "1") {
        if ($level == 1) {
            echo "CON Briefly describe your laptop problem:";
        } else if ($level == 2) {
            $problem = $parts[1];
            $student_id = $student['id'];

            $stmt = $mysqli->prepare("INSERT INTO reports (student_id, problem) VALUES (?, ?)");
            $stmt->bind_param("is", $student_id, $problem);
            $stmt->execute();

            echo "END Your issue has been reported. We'll get back to you soon.";
        }
    }

    else if ($parts[0] == "2") {
        $stmt = $mysqli->prepare("SELECT problem, status FROM reports 
                                  WHERE student_id = ? 
                                  ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("i", $student['id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($report = $result->fetch_assoc()) {
            $status = $report['status'];
            sendSMS($phoneNumber, "Your status is $status.");
            echo "END Last problem: {$report['problem']}\nStatus: $status";
        } else {
            echo "END No reports found. You can submit one first.";
        }
    }

    else {
        echo "END Invalid input. Please try again.";
    }
}

else {
    echo "END Invalid input. Try again.";
}
?>
