@php($isRtl = Session::get('direction') === 'rtl')
<style>
    #viewContactModal-{{ $contact->id }} .bidi-auto { unicode-bidi: plaintext; }
    #viewContactModal-{{ $contact->id }} .bidi-ltr { direction: ltr; unicode-bidi: isolate; display: inline-block; text-align: left; }
</style>
 <div class="modal fade" id="viewContactModal-{{ $contact->id }}" tabindex="-1">
     <div class="modal-dialog modal-lg">
         <div class="modal-content text-start">
             <div class="modal-header">
                 <h5 class="modal-title">{{ translate('Contact Details') }}</h5>
                 <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                     <i class="tio-clear"></i>
                 </button>
             </div>
             <div class="modal-body">
                 <div class="row" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                     <div class="col-md-6">
                         <p><strong>{{ translate('Name') }}:</strong> <span class="bidi-auto">{{ $contact->first_name }} {{ $contact->last_name }}</span></p>
                         <p><strong>{{ translate('Job Title') }}:</strong> <span class="bidi-auto">{{ $contact->job_title }}</span></p>
                         <p><strong>{{ translate('Email') }}:</strong> <span class="bidi-ltr">{{ $contact->email }}</span></p>
                         <p><strong>{{ translate('Phone') }}:</strong> <span class="bidi-ltr">{{ $contact->phone_number }}</span></p>
                         <p><strong>{{ translate('Mobile 1') }}:</strong> <span class="bidi-ltr">{{ $contact->mobile_number_1 }}</span></p>
                         <p><strong>{{ translate('Mobile 2') }}:</strong> <span class="bidi-ltr">{{ $contact->mobile_number_2 }}</span></p>
                     </div>
                     <div class="col-md-6">
                         <p><strong>{{ translate('Address') }}:</strong> <span class="bidi-auto">{{ $contact->address }}, {{ $contact->city }}, {{ $contact->state }}, {{ $contact->country }}</span></p>
                         <p><strong>{{ translate('Preferred Contact Method') }}:</strong> <span class="bidi-auto">{{ $contact->preferred_contact_method }}</span></p>
                         <p><strong>{{ translate('Notes') }}:</strong> <span class="bidi-auto">{{ $contact->notes }}</span></p>
                         <p><strong>{{ translate('Tags') }}:</strong> <span class="bidi-auto">{{ $contact->tags }}</span></p>
                         <p><strong>{{ translate('Last Contacted') }}:</strong> <span class="bidi-ltr">{{ $contact->last_contacted_at }}</span></p>
                         <p><strong>{{ translate('Active') }}:</strong> <span class="bidi-auto">{{ $contact->is_active ? translate('Yes') : translate('No') }}</span></p>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>

