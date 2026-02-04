@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Add Doctor Note</h1>
    <div class="bg-white p-6 rounded shadow">
        <form>
            <div class="mb-4">
                <label class="block text-gray-700">Note</label>
                <textarea class="w-full border rounded px-3 py-2" rows="5" placeholder="Write your note here..."></textarea>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </form>
    </div>
</div>
@endsection
