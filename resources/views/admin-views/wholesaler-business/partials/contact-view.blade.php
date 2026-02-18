 <div class="modal fade" id="viewContactModal-{{ $contact->id }}" tabindex="-1">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">{{ translate('Contact Details') }}</h5>
                 <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="Close">
                     <i class="tio-clear"></i>
                 </button>
             </div>
             <div class="modal-body">
                 <div class="row">
                     <div class="col-md-6">
                         <p><strong>{{ translate('Name') }}:</strong> {{ $contact->first_name }} {{ $contact->last_name }}</p>
                         <p><strong>{{ translate('Job Title') }}:</strong> {{ $contact->job_title }}</p>
                         <p><strong>{{ translate('Email') }}:</strong> {{ $contact->email }}</p>
                         <p><strong>{{ translate('Phone') }}:</strong> {{ $contact->phone_number }}</p>
                         <p><strong>{{ translate('Mobile 1') }}:</strong> {{ $contact->mobile_number_1 }}</p>
                         <p><strong>{{ translate('Mobile 2') }}:</strong> {{ $contact->mobile_number_2 }}</p>
                     </div>
                     <div class="col-md-6">
                         <p><strong>{{ translate('Address') }}:</strong> {{ $contact->address }}, {{ $contact->city }}, {{ $contact->state }}, {{ $contact->country }}</p>
                         <p><strong>{{ translate('Preferred Contact Method') }}:</strong> {{ $contact->preferred_contact_method }}</p>
                         <p><strong>{{ translate('Notes') }}:</strong> {{ $contact->notes }}</p>
                         <p><strong>{{ translate('Tags') }}:</strong> {{ $contact->tags }}</p>
                         <p><strong>{{ translate('Last Contacted') }}:</strong> {{ $contact->last_contacted_at }}</p>
                         <p><strong>{{ translate('Active') }}:</strong> {{ $contact->is_active ? translate('Yes') : translate('No') }}</p>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>