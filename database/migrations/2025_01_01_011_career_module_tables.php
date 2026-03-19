<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CAREER MODULE: Career and Recruitment Management Tables
 *
 * Creates tables for job postings, applications, and recruitment workflow.
 * Matches current database schema as of 2026-03-19.
 *
 * Integration: Career module integrates with Support module via ticket_id
 * - Applications can be converted to support tickets
 * - Interviews, offers, rejections link to tickets for candidate tracking
 */
return new class extends Migration
{
    public function up(): void
    {
        // Career Jobs - Job postings
        Schema::create('career_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('location')->nullable();
            $table->string('experience')->nullable();
            $table->longText('skills')->nullable(); // JSON or comma-separated
            $table->text('job_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Career Sections - CMS sections for career page
        Schema::create('career_sections', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('description')->nullable();
            $table->text('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Career Benefits - Job benefits/perks display
        Schema::create('career_benefits', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Career Cards - Feature cards for career page
        Schema::create('career_cards', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Career Applies - Job applications
        Schema::create('career_applies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('career_jobs')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('notice_period')->nullable();
            $table->string('last_ctc')->nullable();
            $table->string('resume')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Career Interviews - Linked to support tickets
        Schema::create('career_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()->constrained('support_tickets')->onDelete('set null');
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('conducted_at')->nullable();
            $table->longText('panel')->nullable(); // JSON array of interviewer IDs
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Career Activities - Activity log linked to tickets
        Schema::create('career_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->string('activity_type');
            $table->text('description')->nullable();
            $table->longText('attachments')->nullable(); // JSON array of file paths
            $table->foreignId('created_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // Career Offers - Job offers linked to tickets
        Schema::create('career_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->string('attachment')->nullable();
            $table->date('start_date')->nullable();
            $table->datetime('signed_at')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Career Rejections - Rejection records linked to tickets
        Schema::create('career_rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->string('reason_code')->nullable();
            $table->text('closure_message')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Career Talent Pool - Candidates for future opportunities
        Schema::create('career_talent_pool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->boolean('consent')->default(false);
            $table->date('recontact_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_talent_pool');
        Schema::dropIfExists('career_rejections');
        Schema::dropIfExists('career_offers');
        Schema::dropIfExists('career_activities');
        Schema::dropIfExists('career_interviews');
        Schema::dropIfExists('career_applies');
        Schema::dropIfExists('career_cards');
        Schema::dropIfExists('career_benefits');
        Schema::dropIfExists('career_sections');
        Schema::dropIfExists('career_jobs');
    }
};
