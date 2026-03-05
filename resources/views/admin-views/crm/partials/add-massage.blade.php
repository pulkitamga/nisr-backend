<div class="modal fade" id="addMassageModal" tabindex="-1" aria-labelledby="addMassageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.crm.add.massage') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addMassageModalLabel">{{ translate('Add Massage') }}</h5>
                    <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="Close">
                        <i class="tio-clear"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Subject') }} <span class="input-required-icon">*</span>
                            </label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Sender Name') }} <span class="input-required-icon">*</span>
                            </label>
                            <input type="text" class="form-control" name="sender_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Sender Email') }} 
                            </label>
                            <input type="email" class="form-control" name="sender_email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Sender Phone') }} 
                            </label>
                            <input type="text" class="form-control" name="sender_phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Channel') }} <span class="input-required-icon">*</span>
                            </label>
                            <select class="form-control" name="pipeline" required>
                                <option value="email">{{ translate('Email') }}</option>
                                <option value="form">{{ translate('Form') }}</option>
                                <option value="chat">{{ translate('Chat') }}</option>
                                <option value="social">{{ translate('social') }}</option>
                                <option value="phone">{{ translate('Phone') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ translate('Message Type') }} <span class="input-required-icon">*</span>
                            </label>
                            <select class="form-control" name="message_type" required>
                                <option value="support">{{ translate('Support') }}</option>
                                <option value="service">{{ translate('Service') }}</option>
                                <option value="career">{{ translate('Career') }}</option>
                                <option value="warranty">{{ translate('Warranty') }}</option>
                                <option value="contact">{{ translate('Contact') }}</option>
                            </select>
                        </div>
                      
                        <div class="col-12">
                            <label class="form-label">{{ translate('Details') }}
                            </label>
                            <textarea class="form-control" name="details" rows="3"></textarea>
                        </div>

                        <!-- Message -->
                        <div class="col-12">
                            <label class="form-label">{{ translate('Note') }}
                            </label>
                            <textarea class="form-control" name="message" rows="3"></textarea>
                        </div>

                        <!-- Attachment -->
                        <div class="col-12">
                            <label class="form-label">{{ translate('Attachment') }}
                            </label>
                            <input type="file" class="form-control" name="attachment">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
