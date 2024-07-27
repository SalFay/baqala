<div class="favorite-list-item">
    <div data-id="{{ $user->id }}" data-action="0" class="avatar av-m"
        style="background-image: url('{{ asset('/storage/'.config('chatify.user_avatar.folder').'/'.$user->avatar) }}');">
    </div>
    <p>{{ strlen($user->full_name) > 5 ? substr($user->full_name,0,6).'..' : $user->full_name }}</p>
</div>
