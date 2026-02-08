@extends('layouts.app')

@section('title', isset($department) ? 'Edit Department' : 'Create Department')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('departments.index') }}" class="text-blue-600 hover:text-blue-900 mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Departments
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                @if(isset($department))
                    Edit Department
                @else
                    Create New Department
                @endif
            </h1>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ isset($department) ? route('departments.update', $department) : route('departments.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                @if(isset($department))
                    @method('PUT')
                @endif

                <!-- Department Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-900">Department Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $department->name ?? '') }}" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-900">Code</label>
                    <input type="text" name="code" id="code" placeholder="e.g. HR, IT, FIN" value="{{ old('code', $department->code ?? '') }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 @enderror">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-900">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $department->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department Head Name -->
                <div>
                    <label for="head_name" class="block text-sm font-medium text-gray-900">Department Head Name</label>
                    <input type="text" name="head_name" id="head_name" value="{{ old('head_name', $department->head_name ?? '') }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('head_name') border-red-500 @enderror">
                    @error('head_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department Head Position Title -->
                <div>
                    <label for="head_position_title" class="block text-sm font-medium text-gray-900">Department Head Position Title</label>
                    <input type="text" name="head_position_title" id="head_position_title" value="{{ old('head_position_title', $department->head_position_title ?? '') }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('head_position_title') border-red-500 @enderror">
                    @error('head_position_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div>
                    <label for="is_active" class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                            {{ old('is_active', $department->is_active ?? true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm font-medium text-gray-900">Active</span>
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('departments.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                        @if(isset($department))
                            Update Department
                        @else
                            Create Department
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

