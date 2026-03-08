  <div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
          <!-- large modal -->
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">{{ translate('Add Contact') }}</h5>
                  <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                      <i class="tio-clear"></i>
                  </button>
              </div>

              <form action="{{ route('admin.wholesale.business.wholsaler-contect') }}" method="POST">
                  @csrf
                  <input type="hidden" name="company_id" value="{{ $business->id }}">

                  <div class="modal-body">
                      <div class="row mb-3">
                          <div class="col-md-6">
                              <label for="first_name" class="form-label">{{ translate('first_name') }}</label>
                              <input type="text" name="first_name" class="form-control" required>
                          </div>
                          <div class="col-md-6">
                              <label for="last_name" class="form-label">{{ translate('Last Name') }}</label>
                              <input type="text" name="last_name" class="form-control" required>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <div class="col-md-6">
                              <label for="job_title" class="form-label">{{ translate('Job Title') }}</label>
                              <input type="text" name="job_title" class="form-control">
                          </div>
                          <div class="col-md-6">
                              <label for="email" class="form-label">{{ translate('Email') }}</label>
                              <input type="email" name="email" class="form-control" required>
                          </div>
                      </div>

                      <div class="row mb-3">
                          <div class="col-md-6">
                              <label for="phone_number" class="form-label">{{ translate('phone_number') }}</label>
                              <input type="text" name="phone_number" class="form-control">
                          </div>
                          <div class="col-md-6">
                              <label for="mobile_number_1" class="form-label">{{ translate('Mobile Number 1') }}</label>
                              <input type="text" name="mobile_number_1" class="form-control">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <div class="col-md-6">
                              <label for="mobile_number_2" class="form-label">{{ translate('Mobile Number 2') }}</label>
                              <input type="text" name="mobile_number_2" class="form-control">
                          </div>
                          <div class="col-md-6">
                              <label for="preferred_contact_method" class="form-label">{{ translate('Preferred Contact Method') }}</label>
                              <input type="text" name="preferred_contact_method" class="form-control">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <div class="col-md-6">
                              <label for="address" class="form-label">{{ translate('Address') }}</label>
                              <input type="text" name="address" class="form-control">
                          </div>
                          <div class="col-md-6">
                              <label for="city" class="form-label">{{ translate('City') }}</label>
                              <input type="text" name="city" class="form-control">
                          </div>
                      </div>

                      <div class="row mb-3">
                          <div class="col-md-6">
                              <label for="state" class="form-label">{{ translate('State') }}</label>
                              <input type="text" name="state" class="form-control">
                          </div>
                          <div class="col-md-6">
                              <label for="country" class="form-label">{{ translate('Country') }}</label>
                              <input type="text" name="country" class="form-control">
                          </div>
                      </div>

                      <div class="mb-3">
                          <label for="notes" class="form-label">{{ translate('Notes') }}</label>
                          <textarea name="notes" class="form-control" rows="3"></textarea>
                      </div>

                      <div class="mb-3">
                          <label for="tags" class="form-label">{{ translate('Tags') }}</label>
                          <input type="text" name="tags" class="form-control">
                      </div>

                      <div class="row mb-3">
                          <div class="col-md-6">
                              <label class="form-label d-block">{{ translate('Active?') }}</label>
                              <div class="form-check form-switch">
                                  <input type="hidden" name="is_active" value="0"> <!-- fallback -->
                                  <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                  <label class="form-check-label" for="is_active">{{ translate('Yes') }}</label>
                              </div>
                          </div>

                          <div class="col-md-6">
                              <label for="last_contacted_at" class="form-label">{{ translate('Last Contacted At') }}</label>
                              <input type="datetime-local" name="last_contacted_at" class="form-control">
                          </div>
                      </div>
                  </div>

                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                      <button type="submit" class="btn btn--primary">{{ translate('Save Contact') }}</button>
                  </div>
              </form>
          </div>
      </div>
  </div>

