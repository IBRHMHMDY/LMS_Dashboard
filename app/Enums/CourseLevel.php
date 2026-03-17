<?php

namespace App\Enums;

enum CourseLevel: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
    case ALL_LEVELS = 'all_levels';
}