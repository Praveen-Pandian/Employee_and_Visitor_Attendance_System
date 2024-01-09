<?php
session_start();
if (!isset($_SESSION["emp_id"])) {
  header("Location: ../login");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel='icon' href='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSLtc8BZ6ODkts0V0DHZ22rpI9pbM6Erydq3_bk7DWnsA&s' />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homepage</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
  <style>
    #navbar-item {
      background: url(quadsel_logo.png) no-repeat center center;
      background-size: cover;
      width: 130px;
      height: 48px;
    }
  </style>
</head>

<body>
  <nav class="navbar has-shadow" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
      <a class="navbar-item" id="navbar-item" href="./"></a>

      <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="mainNav">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </a>
    </div>

    <div id="mainNav" class="navbar-menu">
      <div class="navbar-end">
        <a class="navbar-item" href="./">
          Analytics
        </a>

        <a class="navbar-item" href="./employee">
          Employee
        </a>
        <a class="navbar-item" href="./visitors">
          Visitors
        </a>
        <a class="navbar-item button is-danger mt-2" href="../logout">
          Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="container p-6">
    <h1 class="title is-1 has-text-centered">Average Check In Time & Check Out Time
    </h1>
    <div class="container is-flex is-centered is-justify-content-center mt-6">
      <table class="table is-bordered is-striped">
        <thead class='has-background-primary'>
          <tr>
            <th>Check-In</th>
            <th>Check-Out</th>
          </tr>
        </thead>
        <tbody>
          <?php
          include_once('./charts/checkinout.php');
          $check_data = getCheckInOutData();
          $check_in_time = "--:--:--";
          $check_out_time = "--:--:--";
          foreach ($check_data as $key => $value) {
            $check_in_time = date('H:i:s', strtotime($value["avg_check_in"]));
            $check_out_time = date('H:i:s', strtotime($value["avg_check_out"]));
          }
          ?>
          <tr>
            <td>
              <?php echo $check_in_time; ?>
            </td>
            <td>
              <?php echo $check_out_time; ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="container is-flex is-centered is-justify-content-center has-text-centered  mt-6">
      <form action="./holidays/index.php" method="post" class="container has-text-centered"
        enctype="multipart/form-data">
        <input type="file" class="input is-info" accept=".xlsx,.xls" name="holiday_data" />
        <button type="submit" class="button is-primary mt-6" name="holiday_submit">Submit</button>
      </form>
    </div>
    <div class="container p-6">
      <h1 class="title is-1 has-text-centered">Employee attendance for
        <?php
        $currentYear = date('Y');
        $currentMonthF = date('F');
        $formattedDate = $currentMonthF . ' ' . $currentYear;
        echo $formattedDate; ?>
      </h1>
      <canvas id="acquisitions"></canvas>
    </div>
  </div>
  <?php
  include_once('./charts/attendance.php');
  $analytics_data = getAttendanceData();
  include_once('./charts/attendance.php');
  $emp_count_data = getEmployeeCount();
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $submit_btn = filter_input(INPUT_POST, 'holiday_submit', FILTER_SANITIZE_STRING);
    if (isset($submit_btn)) {
      $holiday_data = filter_input(INPUT_POST, 'holiday_data', FILTER_SANITIZE_STRING);
      if (isset($holiday_data)) {
        header("Location: ./holidays");
      } else {
        echo "File input not found or upload error.";
      }
    }
  }
  ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      let date = [], emp_count = [], abs_count = [];
      try {
        const TOTAL_EMPLOYEE_COUNT = <?php echo json_encode($emp_count_data); ?>["count"];
        const data = <?php echo json_encode($analytics_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
        date = Object.keys(data);
        emp_count = Object.values(data);
        emp_count.forEach(count => abs_count.push(TOTAL_EMPLOYEE_COUNT - count));
      } catch (error) {
        console.error('Error parsing or processing JSON data:', error);
      }
      new Chart(document.getElementById('acquisitions'), {
        type: 'bar',
        data: {
          datasets: [{
            label: 'Present Count',
            data: emp_count,
            order: 2,
            borderColor: '#36A2EB',
            backgroundColor: '#9BD0F5',
          }, {
            label: 'Absent Count',
            data: abs_count,
            type: 'line',
            order: 1,
            borderColor: '#FF6384',
            backgroundColor: '#FFB1C1',
          }],
          labels: date
        },
      });
      const $navbarBurgers = Array.from(document.querySelectorAll('.navbar-burger'));
      $navbarBurgers.forEach(el => {
        el.addEventListener('click', () => {
          const target = el.dataset.target;
          const $target = document.getElementById(target);
          el.classList.toggle('is-active');
          $target.classList.toggle('is-active');
        });
      });
    });
  </script>
</body>

</html>