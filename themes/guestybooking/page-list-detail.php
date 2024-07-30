<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' />

</head>
<style>
    /* General Reset */
*,
*::after,
*::before {
    padding: 0;
    margin: 0;
    box-sizing: border-box;</header>
}

/* General Paragraph Styling */

h1 {
    margin-bottom: 20px !important;
    padding: 16px 24px 20px;
}

h3 {
    margin: 12px;
}

p {
    font-size: 20px;
    padding: 4px;
}

.dsp p {
    padding: 12px !important;
}

/* Container Styling */
.container {
    padding-inline: 120px;
    margin-bottom: 20px; /* Optional: Add bottom margin to space out sections */
}
.sm-container {
    margin-inline: auto;
}

.top-margin {
    margin-top: 50px;
}

/* Flexbox Layout */
.display-flex {
    display: flex;
    align-items: center;
}

/* Grid Layout */
.display-grid {
    display: grid;
    grid-template-columns: 68% 28%;
    gap: 20px;
}

.dsp-title {
    margin-top: 40px;
}

/* Icon Size */
.icon-size {
    font-size: 18px;
}

/* Header Icons */
.header-icons {
    gap: 30px;
}

.header-icons p {
    padding-left: 20px;
}

/* Row and Column Layout */
.row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px; /* Adjust as needed for spacing between items */
}

.column {
    flex: 1 1 calc(25% - 500px); /* Each column takes up one-fourth of the row, minus some margin */
}

/* Content Icon Margin */
.contents i {
    margin-right: 10px;
}

/* Image Container Styling */
.img-container {
    display: flex;
    overflow-x: auto;
    white-space: nowrap;
    padding: 10px;
}

.img-container img {
    margin-right: 10px; /* Adds space between images */
    height: 100px; /* Set a height for the images */
}

.status-box {
    width: 25px;
    height: 25px;
}

.booked-color { background: #4FAF00 }
.pending-color { background: #DF9A00 }
.external-color { background: #0085BA }
.blocked-color { background: #1D2327 }

.display-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-left: 20px
}

.form-box {
    justify-content: space-between;
    background: #fff;
    border: 1px solid #c3c4c7;
    border-left-width: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
    margin: 5px 15px 16px;
    padding: 1px 12px;
}

.dis-gap {
    gap: 50px
}

.btn-search {
    width: 80px;
    height: 20px;
    text-align: center;
}

th, td {
    border: 0.5px solid #F0F0F1;
    background-color: #fff;;
}
.cal-days {
    font-size: 10px;
}
.cal-days p {
    margin: 0px !important;
}
.cal-days #year, .cal-days #week {
    font-weight: 500;
    font-size: 16px;
    opacity: 60%;
}

.cal-days #day, .cal-days #month { 
    opacity: 90%;
    font-weight: 700;
}

.cal-days #week {
    font-size: 12px;
}

.day-mark {
    width: 20px;
    height: 20px;
}

.table-wrapper {
    overflow-x: auto;
    width: 100%;
}

.acm-size {
    text-align: center;
    font-size: 17px;
    padding-top: 8px;
    padding-bottom: 8px;
}

.form-table th { text-align: center !important; }

.loader-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 76vh;
}

.loader {
  display: flex;
  justify-content: space-between;
  width: 80px;
}

.loader div {
  width: 16px;
  height: 16px;
  background-color: #FF5C35;
  border-radius: 50%;
  animation: grow-shrink 1.5s infinite;
}

.loader div:nth-child(1) {
  animation-delay: 0s;
}

.loader div:nth-child(2) {
  animation-delay: 0.3s;
}

.loader div:nth-child(3) {
  animation-delay: 0.6s;
}

@keyframes grow-shrink {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.5);
  }
}

.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    padding-top: 100px; /* Location of the box */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
  }
  
  /* Modal Content */
  .modal-content {
    position: relative;
    background-color: #fefefe;
    margin: auto;
    padding: 0;
    border: 1px solid #888;
    width: 80%;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
    -webkit-animation-name: animatetop;
    -webkit-animation-duration: 0.4s;
    animation-name: animatetop;
    animation-duration: 0.4s
  }
  
  /* Add Animation */
  @-webkit-keyframes animatetop {
    from {top:-300px; opacity:0} 
    to {top:0; opacity:1}
  }
  
  @keyframes animatetop {
    from {top:-300px; opacity:0}
    to {top:0; opacity:1}
  }
  
  /* The Close Button */
  .close {
    color: white;
    float: right;
    font-size: 28px;
    font-weight: bold;
  }
  
  .close:hover,
  .close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
  }
  
  .modal-header {
    padding: 2px 16px;
    background-color: #5cb85c;
    color: white;
  }
  
  .modal-body {padding: 2px 16px;}
  
  .modal-footer {
    padding: 2px 16px;
    background-color: #5cb85c;
    color: white;
  }
</style>
<body>
    
<?php
    echo do_shortcode('[get_detail_list]');
    get_footer(); 
?>
</body>
</html>