<?php
declare(strict_types=1);

/**
 * @var \Cake\ORM\ResultSet<\Fr3nch13\Utilities\Model\Entity\Course> $courses
 */
?>

<ul class="courses">
 <?php foreach ($courses as $course): ?>
   <li class="course">
        <span class="id"><?php echo $course->get('id'); ?></span>
        <span class="name"><?php echo $course->get('name'); ?></span>
    </li>
 <?php endforeach; ?>
 </ul>
