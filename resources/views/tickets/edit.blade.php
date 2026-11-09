@extends('components.layout')
@section('content')
    <section class="page">
        <div class="page-head">
            <h1>Edit Ticket</h1>
            <p>Update the ticket information.</p>
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
        <form class="form-card" id="ticketForm" method="POST" action="{{ route('ticket.update', $ticket) }}" data-cancel-url="{{ route('index') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="description" id="descriptionInput" value="{{ old('description', $ticket->description) }}">
            <input type="hidden" name="priority" id="priorityInput" value="{{ old('priority', strtolower($ticket->ticket_priority)) }}">
            @can('updateStatus', $ticket)
                <input type="hidden" name="status" id="statusInput" value="{{ old('status', ['in_progress' => 'pending', 'close' => 'closed', 'resolved' => 'closed'][$ticket->status] ?? $ticket->status) }}">
            @endcan
            <div class="field" id="titleField"><label for="title">Title <span class="required">*</span></label><input
                    class="title-input" id="title" name="title" maxlength="160"
                    placeholder="Enter a short and clear title" value="{{ old('title', $ticket->ticket_name) }}" required />
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
                    <div id="description" class="editor-content" contenteditable="true" role="textbox" aria-multiline="true"
                        aria-labelledby="descriptionLabel" data-placeholder="Please provide as much detail as possible..."></div>
                </div>
                <p class="form-error">Please describe the issue you're experiencing.</p>
            </div>
            <div class="priority-field"><span class="priority-label">Priority <span class="required">*</span></span>
                <div class="priority-options" role="group" aria-label="Ticket priority"><button class="priority low"
                        data-priority="low" type="button" aria-pressed="false">◈ Low</button><button
                        class="priority medium selected" data-priority="medium" type="button"
                        aria-pressed="true">Medium</button><button class="priority high" data-priority="high" type="button"
                        aria-pressed="false">✓ High</button></div>
            </div>
            @can('updateStatus', $ticket)
            <div class="status-field"><span class="priority-label">Status <span class="required">*</span></span>
                <div class="status-options" role="group" aria-label="Ticket status"><button class="status open selected"
                        data-status="open" type="button" aria-pressed="true">◉ Open</button><button class="status progress"
                        data-status="pending" type="button" aria-pressed="false">◷ Pending</button><button
                        class="status closed" data-status="closed" type="button" aria-pressed="false">✓ Close</button>
                </div>
            </div>
            @else
                <div class="status-field">
                    <span class="priority-label">Status</span>
                    <x-ticket-badge :value="$ticket->status" />
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Only admins can change ticket status.</p>
                </div>
            @endcan
            <div class="actions"><button class="btn btn-secondary" id="cancel" type="button">Cancel</button><button
                    class="btn btn-primary" id="submit" type="submit">Save Changes</button></div>
        </form>
    </section>
@endsection

