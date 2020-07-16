<!-- 每次切換語系，都會導向 /language 設定路由，並導回原本頁面 redirect()->back() -->
<form method="POST" action="{{ URL::to('/language') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <label>{{ trans("global.language") }}：</label>
    <select type="submit" name="language" onchange="this.form.submit()">
    	<option value="en"  @if (App::getLocale() === "en")  selected @endif>English</option>
        <option value="cht" @if (App::getLocale() === "cht") selected @endif>繁體中文</option>
    </select>
</form>
