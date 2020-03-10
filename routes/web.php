<?php

Route::get('login', ['as' => 'login', 'uses' => 'Auth\LoginController@showLoginForm']);
Route::post('login', ['as' => '', 'uses' => 'Auth\LoginController@login']);
Route::post('logout', ['as' => 'logout', 'uses' => 'Auth\LoginController@logout']);

Route::get('failed', function (){ return view('site.failed'); })->name('failed')->middleware('auth');

Route::group(['namespace' => 'Admin', 'middleware' => ['auth', 'admin'], 'as' => 'admin.'], function () {
    Route::get('', function (){return view('admin.home');})->name('home');

    //QA refers to Questions & Answers
    Route::group(['prefix' => 'qa', 'as' => 'qa.'], function () {
        Route::resource('question', 'QA\QuestionController', ['except' => ['show', 'edit', 'update', 'store']]);
        Route::resource('answer', 'QA\AnswerController', ['except' => ['show', 'edit', 'update', 'store']]);
        Route::resource('theme', 'QA\ThemeController', ['except' => ['show']]);
        Route::resource('comment', 'QA\CommentController', [
            'except' => ['show', 'edit', 'update', 'store', 'create'],
        ]);
    });

    //KB refers to Knowledge Base
    Route::group(['namespace' => 'KB', 'prefix' => 'kb', 'as' => 'kb.'], function () {
        Route::resource('article', 'ArticleController', ['except' => ['show']]);
        Route::resource('theme', 'ThemeController', ['except' => ['show']]);
        Route::resource('comment', 'CommentController', [
            'except' => ['show', 'edit', 'update', 'store', 'create'],
        ]);
    });

    //CL refers to Courses & Lessons
    Route::group(['namespace' => 'CL', 'prefix' => 'cl', 'as' => 'cl.'], function () {
        //Comments
        Route::resource('course/comment', 'CourseCommentController', [
            'except' => ['show', 'edit', 'update', 'store', 'create'],
            'as' => 'course'
        ]);
        Route::resource('lesson/comment', 'LessonCommentController', [
            'except' => ['show', 'edit', 'update', 'store', 'create'],
            'as' => 'lesson'
        ]);

        Route::resource('course/author', 'AuthorController', ['except' => ['show']]);
        Route::resource('course', 'CourseController', ['except' => ['show']]);
        Route::resource('lesson/file', 'LessonFileController', ['except' => ['show']]);
        Route::resource('lesson', 'LessonController', ['except' => ['show']]);
        Route::resource('theme', 'ThemeController', ['except' => ['show']]);
        //Tests
        Route::get('tests', 'TestController@indexCourses')->name('test-courses.index');
        Route::get('tests/{course}/lessons', 'TestController@indexLessons')->name('test-lessons.index');
        Route::get('tests/{course}/lessons/{lesson}', 'TestController@index')->name('test.index');
        Route::get('tests/{lesson}/create', 'TestController@store')->name('test.create');
        Route::delete('tests/delete/{test}', 'TestController@destroy')->name('test.destroy');
        //Questions
        Route::get('tests/{test}/questions', 'QuestionController@index')->name('questions.index');
        Route::get('tests/{test}/questions/create', 'QuestionController@create')->name('questions.create');
        Route::post('tests/questions', 'QuestionController@store')->name('questions.store');
        Route::get('questions/{question}/edit', 'QuestionController@edit')->name('questions.edit');
        Route::patch('questions/{question}', 'QuestionController@update')->name('questions.update');
        Route::delete('questions/{question}', 'QuestionController@destroy')->name('questions.destroy');

        //Answers
        Route::get('questions/{question}/answers', 'AnswerController@index')->name('answers.index');
        Route::get('questions/{question}/answers/create', 'AnswerController@create')->name('answers.create');
        Route::post('questions/answers', 'AnswerController@store')->name('answers.store');
        Route::get('answers/{answer}/edit', 'AnswerController@edit')->name('answers.edit');
        Route::patch('answers/{answer}', 'AnswerController@update')->name('answers.update');
        Route::delete('answers/{answer}', 'AnswerController@destroy')->name('answers.destroy');
    });
});