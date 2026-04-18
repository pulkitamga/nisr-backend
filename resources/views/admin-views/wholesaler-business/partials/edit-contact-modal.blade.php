 <div class="modal fade" id="editContactModal-{{ $contact->id }}" tabindex="-1">
     <div class="modal-dialog modal-lg">
         <form action="{{ route('admin.wholesale.business.wholsaler-contect.update', $contact->id) }}" method="POST">
             @csrf
             @method('PUT')
             <div class="modal-content">
                 <div class="modal-header">
                     <h5 class="modal-title">{{ translate('Edit Contact') }}</h5>
                     <button type="button" class="radius-50 btn-close border-0" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                         <i class="tio-clear"></i>
                     </button>
                 </div>

                 <div class="modal-body row g-3">
                     <div class="col-md-6">
                         <label>{{ translate('first_Name') }}</label>
                         <input type="text" name="first_name" class="form-control" value="{{ $contact->first_name }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('last_Name') }}</label>
                         <input type="text" name="last_name" class="form-control" value="{{ $contact->last_name }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Email') }}</label>
                         <input type="email" name="email" class="form-control" value="{{ $contact->email }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('phone_Number') }}</label>
                         <input type="text" name="phone_number" class="form-control" value="{{ $contact->phone_number }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Mobile Number 1') }}</label>
                         <input type="text" name="mobile_number_1" class="form-control" value="{{ $contact->mobile_number_1 }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Mobile Number 2') }}</label>
                         <input type="text" name="mobile_number_2" class="form-control" value="{{ $contact->mobile_number_2 }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Job Title') }}</label>
                         <input type="text" name="job_title" class="form-control" value="{{ $contact->job_title }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Preferred Contact Method') }}</label>
                         <input type="text" name="preferred_contact_method" class="form-control" value="{{ $contact->preferred_contact_method }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Address') }}</label>
                         <input type="text" name="address" class="form-control" value="{{ $contact->address }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('City') }}</label>
                         <input type="text" name="city" class="form-control" value="{{ $contact->city }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('State') }}</label>
                         <input type="text" name="state" class="form-control" value="{{ $contact->state }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Country') }}</label>
                         <input type="text" name="country" class="form-control" value="{{ $contact->country }}">
                     </div>

                     <div class="col-md-12">
                         <label>{{ translate('Notes') }}</label>
                         <textarea name="notes" class="form-control">{{ $contact->notes }}</textarea>
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Tags') }}</label>
                         <input type="text" name="tags" class="form-control" value="{{ $contact->tags }}">
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Is Active') }}</label>
                         <select name="is_active" class="form-select">
                             <option value="1" {{ $contact->is_active ? 'selected' : '' }}>{{ translate('Yes') }}</option>
                             <option value="0" {{ !$contact->is_active ? 'selected' : '' }}>{{ translate('No') }}</option>
                         </select>
                     </div>

                     <div class="col-md-6">
                         <label>{{ translate('Last Contacted At') }}</label>
                         <input type="datetime-local" name="last_contacted_at" class="form-control"
                             value="{{ $contact->last_contacted_at ? date('Y-m-d\TH:i', strtotime($contact->last_contacted_at)) : '' }}">
                     </div>
                 </div>

                 <div class="modal-footer">
                     <button class="btn--primary" type="submit">{{ translate('Update') }}</button>
                 </div>
             </div>
         </form>
     </div>
 </div>

