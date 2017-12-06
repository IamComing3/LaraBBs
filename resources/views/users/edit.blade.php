@extends('layouts.app')
@section('title', '编辑个人资料')
@section('content')

    <div class="container">
        <div class="panel panel-default col-md-10 col-md-offset-1">
            <div class="panel-heading">
                <h4>
                    <i class="glyphicon glyphicon-edit"></i> 编辑个人资料
                </h4>
            </div>

            @include('common.error')

            <div class="panel-body">

                <form action="{{ route('users.update', $user->id) }}" method="post" accept-charset="UTF-8">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}

                    <div class="form-group">
                        <label for="name-field">用户名</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" id="name-field">
                    </div>

                    <div class="form-group">
                        <label for="email-field">邮 箱</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" id="email-field">
                    </div>

                    <div class="form-group">
                        <label for="introduction-field">个人简介</label>
                        <textarea name="introduction" rows="3" class="form-control" id="introduction-field">{{ old('introduction', $user->introduction) }}</textarea>
                    </div>

                    <div class="well well-sm">
                        <button type="submit" name="button" class="btn btn-primary">保存</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@stop