<?php

Route::group(['namespace' => 'API', 'middleware' => 'api', 'prefix' => 'auth', 'as' => 'auth.'], function () {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('resend', 'AuthController@resend');     //resend verification code
    Route::post('verify', 'AuthController@verify');
});

Route::group(['namespace' => 'API', 'middleware' => 'api', 'prefix' => 'password'], function () {
    Route::post('create', 'PasswordResetController@create');
    Route::post('verify', 'PasswordResetController@verify');
    Route::post('reset', 'PasswordResetController@reset');
});


Route::group(['middleware' => 'auth:api'], function(){
    Route::get('user/info', 'API\UserController@info');
    Route::get('profile/info', 'API\ProfileController@info');
    Route::put('profile/update', 'API\ProfileController@update');
    Route::delete('profile/avatar/delete', 'API\ProfileController@deleteUserAvatar');

    Route::get('profile/questions', 'API\ProfileController@questions');
    Route::get('profile/answers', 'API\ProfileController@answers');
    Route::get('profile/qa/favorite', 'API\ProfileController@qaFavorite');
    Route::get('profile/kb/favorite', 'API\ProfileController@kbFavorite');
    Route::get('profile/qa/comments', 'API\ProfileController@qaComments');
    Route::get('profile/kb/comments', 'API\ProfileController@kbComments');
    Route::get('profile/cl/courses', 'API\ProfileController@courses');

    Route::post('user/fav/question/add', 'API\UserController@addFavQuestion');
    Route::post('user/fav/theme/add', 'API\UserController@addFavQATheme');
    Route::post('user/fav/article/add', 'API\UserController@addFavArticle');
    Route::post('user/fav/kb-theme/add', 'API\UserController@addFavKBTheme');

    Route::post('user/fav/question/remove', 'API\UserController@removeFavQuestion');
    Route::post('user/fav/theme/remove', 'API\UserController@removeFavQATheme');
    Route::post('user/fav/article/remove', 'API\UserController@removeFavArticle');
    Route::post('user/fav/kb-theme/remove', 'API\UserController@removeFavKBTheme');

    Route::post('user/specialization/approve', 'API\UserController@approveUserSpecialization');
    Route::post('user/specialization/disapprove', 'API\UserController@disapproveUserSpecialization');

    Route::group(['prefix' => 'qa-moderation', 'as' => 'qa-moderation.'], function () {
        Route::resource('question', 'API\QA\Moderation\QuestionController', ['except' => ['index', 'create', 'edit', 'show']]);
        Route::post('question/{question}/rate', 'API\QA\Moderation\QuestionController@rate');

        Route::resource('answer', 'API\QA\Moderation\AnswerController', ['except' => ['index', 'create', 'edit', 'show']]);
        Route::post('answer/{answer}/rate', 'API\QA\Moderation\AnswerController@rate');
        Route::post('select/{answer}/question/{question}', 'API\QA\Moderation\AnswerController@selectRightAnswer');

        Route::resource('comment', 'API\QA\Moderation\CommentController', ['except' => ['index', 'create', 'edit', 'show']]);
        Route::post('comment/{comment}/rate', 'API\QA\Moderation\CommentController@rate');
    });

    Route::group(['namespace' => 'API\KB\Moderation', 'prefix' => 'kb-moderation', 'as' => 'kb-moderation.'], function () {
        Route::post('article/{article}/rate', 'ArticleController@rate');

        Route::resource('comment', 'CommentController', ['except' => ['index', 'create', 'edit', 'show']]);
        Route::post('comment/{comment}/rate', 'CommentController@rate');
    });

    Route::group(['namespace' => 'API\CL\Moderation', 'prefix' => 'cl-moderation', 'as' => 'cl-moderation.'], function () {
        Route::post('course/{course}/rate', 'CourseController@rate');
        Route::resource('course/comment', 'CourseCommentController', ['except' => ['index', 'create', 'edit', 'show']]);
        Route::post('course/comment/{comment}/rate', 'CourseCommentController@rate');

        Route::post('lesson/{lesson}/rate', 'LessonController@rate');
        Route::resource('lesson/comment', 'LessonCommentController', ['except' => ['index', 'create', 'edit', 'show']]);
        Route::post('lesson/comment/{comment}/rate', 'LessonCommentController@rate');
    });

    //Achievements
    Route::get('achievement', 'AchievementController@index')->name('achievement.index');
    Route::get('achievement/{achievement}', 'AchievementController@show')->name('achievement.show');
});

Route::group(['namespace' => 'API', 'middleware' => 'api'], function () {
    Route::get('user/{user}', 'UserController@user');
    Route::get('user/{user}/info', 'UserController@userInfo');

    Route::get('user/{user}/questions', 'UserController@questions');
    Route::get('user/{user}/answers', 'UserController@answers');
    Route::get('user/{user}/qa/comments', 'UserController@qaComments');
    Route::get('user/{user}/kb/comments', 'UserController@kbComments');
    Route::get('user/{user}/cl/courses', 'UserController@courses');
    //User achievements endpoints
    Route::get('user/achievements/{user}', 'UserController@getAchievements');
    Route::get('user/achievements/profile-fill-progress/get', 'UserController@getProgress');

    Route::get('profile/specialization/available', 'GetController@getSpecializations');
    Route::get('profile/user-types/available', 'GetController@getUserTypes');

    Route::get('profile/region/available', 'GetController@getRegions');
    Route::get('profile/district/available', 'GetController@getDistricts');
    Route::get('profile/locality/available', 'GetController@getLocalities');
    Route::get('profile/school/available', 'GetController@getSchools');

    Route::get('profile/job/domain/available', 'GetController@getJobDomains');

    Route::post('user/course/start/{course}', 'CL\CourseController@start');
    Route::post('user/lesson/start/{lesson}', 'CL\LessonController@start');
    Route::post('user/lesson/finish/{lesson}', 'CL\LessonController@finish');

    Route::get('cl/authors/available', 'GetController@getCLAuthors');
});

Route::group(['prefix' => 'qa', 'as' => 'qa.'], function () {
    Route::resource('question', 'API\QA\QuestionController', ['except' => ['create', 'edit', 'store', 'update', 'destroy']]);
    Route::get('interesting', 'API\QA\QuestionController@interesting');
    Route::get('my-list', 'API\QA\QuestionController@myList');
    Route::get('answers-leader', 'API\QA\QuestionController@answersLeader');
    Route::get('similar-questions/{question}', 'API\QA\QuestionController@similarQuestions');
    Route::get('theme', 'API\QA\QuestionController@getThemes');
    Route::get('locale', 'API\QA\QuestionController@getLocales');
    Route::get('count', 'API\QA\QuestionController@getQACount');
});

Route::group(['namespace' => 'API\KB', 'prefix' => 'kb', 'as' => 'kb.'], function () {
    Route::get('article/interesting', 'ArticleController@interesting');
    Route::resource('article', 'ArticleController', ['except' => ['create', 'edit', 'store', 'update', 'destroy']]);
    Route::get('similar-articles/{article}', 'ArticleController@similarArticles');
    Route::get('theme', 'ArticleController@getThemes');
    Route::get('locale', 'ArticleController@getLocales');
});

Route::group(['namespace' => 'API\CL', 'prefix' => 'cl', 'as' => 'cl.'], function () {
    Route::get('course/statuses', 'CourseController@getStatuses');
    Route::get('course/themes', 'CourseController@getThemes');
    Route::resource('course', 'CourseController', ['except' => ['create', 'edit', 'store', 'update', 'destroy']]);

    Route::get('lesson/{lesson}', 'LessonController@show');

    Route::get('tests/{lesson}', 'TestController@show');
    Route::post('tests/{test}', 'TestController@startTest');
    Route::post('tests/{lesson}/{test}', 'TestController@endTest');
    Route::get('tests/is-success/{lesson}', 'TestController@isSuccess');
    Route::get('tests/statistic/{test}', 'TestController@getStatistic');
    Route::get('tests/statistic/brief/{test}', 'TestController@getBriefStatistics');
    //Test moderation API.
    Route::get('moderation/tests/{lesson}', 'Moderation\TestController@index');
    Route::get('moderation/tests/show/{test}', 'Moderation\TestController@show');
    Route::post('moderation/tests', 'Moderation\TestController@store');
    Route::patch('moderation/tests/{test}', 'Moderation\TestController@update');
    Route::delete('moderation/tests/{test}', 'Moderation\TestController@destroy');
    //Question moderation API.
    Route::get('moderation/questions/{test}', 'Moderation\QuestionController@index');
    Route::get('moderation/questions/show/{question}', 'Moderation\QuestionController@show');
    Route::post('moderation/questions', 'Moderation\QuestionController@store');
    Route::patch('moderation/questions/{question}', 'Moderation\QuestionController@update');
    Route::delete('moderation/questions/{question}', 'Moderation\QuestionController@destroy');
    //Answer moderation API.
    Route::get('moderation/answers/{question}', 'Moderation\AnswerController@index');
    Route::get('moderation/answers/show/{answer}', 'Moderation\AnswerController@show');
    Route::post('moderation/answers/{question}', 'Moderation\AnswerController@store');
    Route::patch('moderation/answers/{answer}', 'Moderation\AnswerController@update');
    Route::delete('moderation/answers/{answer}', 'Moderation\AnswerController@destroy');
});

//Admin routes
Route::group(['middleware' => ['auth:api', 'api.admin']], function() {
    Route::group(['namespace' => 'API\KB\Moderation', 'prefix' => 'kb-moderation', 'as' => 'kb-moderation.'], function () {
        Route::resource('article', 'ArticleController', ['except' => ['index', 'create', 'edit', 'show']]);
    });

    Route::group(['namespace' => 'API\CL\Moderation', 'prefix' => 'cl-moderation', 'as' => 'cl-moderation.'], function () {
        Route::resource('course', 'CourseController', ['except' => ['index', 'create', 'edit', 'show']]);
        Route::delete('course/video/{course}', 'CourseController@deleteVideo');

        Route::resource('lesson/file', 'LessonFileController', ['except' => ['index', 'create', 'edit', 'show']]);
        Route::resource('lesson', 'LessonController', ['except' => ['index', 'create', 'edit', 'show']]);

        Route::resource('author', 'AuthorController', ['except' => ['index', 'create', 'edit', 'show']]);
    });

    Route::group(['namespace' => 'API\Statistics', 'prefix' => 'statistics', 'as' => 'statistics.'], function () {
        //Users statistics
        Route::get('users', 'UserStatisticsController@user');
        Route::get('users/contents', 'UserStatisticsController@content');
        Route::get('users/auditory', 'UserStatisticsController@auditory');

        Route::get('users/courses/{user}', 'InfoStatisticsController@userCourses');
        Route::get('users/statistics/{user}', 'InfoStatisticsController@userStatistics');
        Route::get('users/info/{user}', 'InfoStatisticsController@userInfo');
        Route::get('users/index', 'InfoStatisticsController@index');

        //Courses & Lessons statistics
        Route::get('cl/lesson/{lesson}', 'CourseStatisticsController@lesson');
        Route::get('cl/course/{course}', 'CourseStatisticsController@course');
    });
});

//Atameken admin routes
Route::group(['middleware' => ['api.atameken'], 'prefix' => 'atameken', 'as' => 'atameken.'], function() {
    Route::group(['namespace' => 'API\Statistics', 'prefix' => 'statistics', 'as' => 'statistics.'], function () {

        Route::get('school/student', 'SchoolStatisticsController@indexStudent');
        Route::get('school/student/{user}', 'SchoolStatisticsController@student');
        Route::get('school/student/{user}/course/{course}', 'SchoolStatisticsController@studentCourse');

    });
});