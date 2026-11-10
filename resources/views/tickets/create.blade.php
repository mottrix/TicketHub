@extends('components.layout')
@section('content')
    
    <section class="page">
        <div class="page-head">
            <h1>Create New Ticket</h1>
            <p>Describe your issue and we'll get back to you soon.</p>
        </div>
        <div class="notice" id="notice" role="status"></div>
        @if ($errors->any())
            <div role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{-- The shared editor script uses these IDs to populate named hidden fields.
             old() restores submitted values after Laravel redirects back with validation errors. --}}
        <form class="form-card" id="ticketForm" method="POST" action="{{ route('ticket.store') }}" data-cancel-url="{{ route('index') }}">
            @csrf
            <input type="hidden" name="description" id="descriptionInput" value="{{ is_string(old('description')) ? old('description') : '' }}">
            <input type="hidden" name="priority" id="priorityInput" value="{{ is_string(old('priority', 'medium')) ? old('priority', 'medium') : 'medium' }}">
            <div class="field" id="titleField"><label for="title">Title <span class="required">*</span></label><input
                    class="title-input" id="title" name="title" maxlength="160" value="{{ is_string(old('title')) ? old('title') : '' }}"
                    placeholder="Enter a short and clear title" required />
                <p class="form-error">Please enter a title for your ticket.</p>
            </div>
            <div class="field" id="descriptionField"><label id="descriptionLabel">Description <span
                        class="required">*</span></label>
                <div class="editor">
                    <div class="toolbar" role="toolbar" aria-label="Description formatting"><button type="button"
                            class="tool" data-command="bold" title="Bold"
                            aria-label="Bold"><strong>B</strong></button><button type="button" class="tool"
                            data-command="italic" title="Italic" aria-label="Italic"><em>I</em></button><button
                            type="button" class="tool" data-command="insertUnorderedList" title="Bulleted list"
                            aria-label="Bulleted list"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M8 6h12M8 12h12M8 18h12" />
                                <path d="M3 6h2M3 12h2M3 18h2" />
                            </svg></button><button type="button" class="tool" data-command="insertOrderedList"
                            title="Numbered list" aria-label="Numbered list"><svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M9 6h11M9 12h11M9 18h11" />
                                <path d="M3 5h2v4M3 12h3l-3 3h3" />
                            </svg></button><button type="button" class="tool" id="linkTool" title="Insert link"
                            aria-label="Insert link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="m10 13 4-4M8 16H7a4 4 0 0 1 0-8h4m2 0h4a4 4 0 1 1 0 8h-4" />
                            </svg></button></div>
                    {{-- This editable div has no form value; JavaScript copies its HTML to descriptionInput. --}}
                    <div id="description" class="editor-content" contenteditable="true" role="textbox" aria-multiline="true"
                        aria-labelledby="descriptionLabel" data-placeholder="Please provide as much detail as possible...">
                    </div>
                </div>
                <p class="form-error">Please describe the issue you're experiencing.</p>
            </div>
            <div class="priority-field"><span class="priority-label">Priority <span class="required">*</span></span>
                <div class="priority-options" role="group" aria-label="Ticket priority"><button class="priority low"
                        data-priority="low" type="button" aria-pressed="false">◈ Low</button><button
                        class="priority medium selected" data-priority="medium" type="button"
                        aria-pressed="true">Medium</button><button class="priority high" data-priority="high"
                        type="button" aria-pressed="false">✓ High</button></div>
            </div>
            <div class="actions"><button class="btn btn-secondary" id="cancel" type="button">Cancel</button><button
                    class="btn btn-primary" id="submit" type="submit">Create Ticket</button></div>
        </form>
    </section>
@endsection

