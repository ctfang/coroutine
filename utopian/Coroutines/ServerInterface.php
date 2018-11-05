<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/11/1
 * Time: 20:28
 */

namespace Utopian\Coroutines;


use Utopian\Http\Server\Request;
use Utopian\Http\Server\Response;

interface ServerInterface
{
    public function handle(Request $request):Response;
}