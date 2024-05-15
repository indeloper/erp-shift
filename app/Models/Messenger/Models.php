<?php

namespace App\Models\Messenger;

use Lexx\ChatMessenger\Models\Message;
use Lexx\ChatMessenger\Models\Participant;
use Lexx\ChatMessenger\Models\Thread;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Models
{
    /**
     * Map for the messenger's models.
     *
     * @var array
     */
    protected static $models = [];

    /**
     * Map for the messenger's tables.
     *
     * @var array
     */
    protected static $tables = [];

    /**
     * Internal pointer name for the app's "user" model.
     *
     * @var string
     */
    private static $userModelLookupKey = 'User';

    /**
     * Set the model to be used for threads.
     */
    public static function setMessageModel(string $model)
    {
        static::$models[Message::class] = $model;
    }

    /**
     * Set the model to be used for participants.
     */
    public static function setParticipantModel(string $model)
    {
        static::$models[Participant::class] = $model;
    }

    /**
     * Set the model to be used for threads.
     */
    public static function setThreadModel(string $model)
    {
        static::$models[Thread::class] = $model;
    }

    /**
     * Set the model to be used for users.
     */
    public static function setUserModel(string $model)
    {
        static::$models[self::$userModelLookupKey] = $model;
    }

    /**
     * Set custom table names.
     */
    public static function setTables(array $map)
    {
        static::$tables = array_merge(static::$tables, $map);
    }

    /**
     * Get a custom table name mapping for the given table.
     */
    public static function table(string $table): string
    {
        if (isset(static::$tables[$table])) {
            return static::$tables[$table];
        }

        return $table;
    }

    /**
     * Get the class name mapping for the given model.
     */
    public static function classname(string $model): string
    {
        if (isset(static::$models[$model])) {
            return static::$models[$model];
        }

        return $model;
    }

    /**
     * Get an instance of the messages model.
     */
    public static function message(array $attributes = []): Message
    {
        return static::make(Message::class, $attributes);
    }

    /**
     * Get an instance of the participants model.
     */
    public static function participant(array $attributes = []): Participant
    {
        return static::make(Participant::class, $attributes);
    }

    /**
     * Get an instance of the threads model.
     */
    public static function thread(array $attributes = []): Thread
    {
        return static::make(Thread::class, $attributes);
    }

    /**
     * Get an instance of the user model.
     */
    public static function user(array $attributes = []): Model
    {
        return static::make(User::class, $attributes);
    }

    /**
     * Get an instance of the given model.
     */
    protected static function make(string $model, array $attributes = []): Model
    {
        $model = static::classname($model);

        return new $model($attributes);
    }
}
