@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="row subscription-row mt-4">

            @php
                $currentPlan = app('subscription_helper')->get_current_subscription();
            @endphp

            @foreach ($plans as $plan)
                <div class="col-sm-4">
                    <div class="card subscription-card p-4" style="position: relative;">
                        @if ($currentPlan && $currentPlan->subscription_plan_price_id == $plan->stripe_price_id)
                            <span class="badge badge-pill badge-success badge-active-align">
                                Active
                            </span>
                        @endif
                        <h2 class="p-3">
                            {{ $plan->name }}
                        </h2>
                        <h4 class="pb-4">${{ number_format($plan->amount, 0) }} Charge</h4>
                        @if ($currentPlan && $currentPlan->subscription_plan_price_id == $plan->stripe_price_id)
                            @if ($currentPlan->plan_interval != 'lifetime')
                                <button class="btn btn-danger btn-shape subscriptionCancel">Cancel</button>
                            @else
                                <button class="btn btn-success btn-shape disabled-btn">Subscribed</button>
                            @endif
                        @else
                            <button
                                class="btn btn-primary confirmationBtn
                            @if ($currentPlan && $currentPlan->plan_interval == 'lifetime') disabled-btn @endif"
                                data-toggle="modal" data-target="#confirmationModal"
                                data-id="{{ $plan->id }}">Subscribe</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalTitle">...</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="confirmation-data">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary continueBuyPlan">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stripe Modal -->
    <div class="modal fade" id="stripeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stripeModalTitle">Buy Subscription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="planId" id="planId" />
                    {{-- Stripe Card Element --}}
                    <div id="card-element"></div>
                    {{-- show card errors --}}
                    <div id="card-errors" style="color:red;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="buyPlanSubmitBtn" class="btn btn-primary">Buy Plan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- stripe card elemenst load --}}
    <script src="https://js.stripe.com/v3/"></script>

    <script>
        // ----------------- PLAN CONFIRMATION -----------------
        $(document).ready(function() {
            // confirmation modal open and manage
            $('.confirmationBtn').click(function() {
                $('#confirmationModalTitle').text('...');
                $('.confirmation-data').html('<i class="fa fa-spinner fa-spin"></i>');
                var planID = $(this).data('id');
                $('#planId').val(planID);

                $.ajax({
                    type: "POST",
                    url: "{{ route('getPlanDetails') }}",
                    data: {
                        id: planID,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            var html = '';
                            $('#confirmationModalTitle').text(data.name + ' ($' + data
                                .amount + ')');
                            html += `<p>` + response.message + `</p>`;
                            $('.confirmation-data').html(html);
                        } else {
                            alert('Something went wrong!');
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.statusText);
                    }
                });
            });

            // stripe plan modal open
            $('.continueBuyPlan').click(function() {
                $('#confirmationModal').modal('hide');
                $('#stripeModal').modal('show');
            });

            /*------------- subscription cancel ---------------- */
            $('.subscriptionCancel').click(function() {
                var obj = $(this);
                $(obj).html(
                    'Please Wait <i class="fa fa-spinner fa-spin" style="font-size:24px !important;"></i>'
                );
                $(obj).attr('disabled','disabled');

                $.ajax({
                    type: "POST",
                    url: "{{ route('cancelSubscription') }}",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            window.location.reload();
                        } else {
                            alert('Something went wrong!');
                            $(obj).html('Cancel');
                             $(obj).removeAttr('disabled');
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.statusText);
                    }
                });
            });
            /* -------------------------------------------------- */
        });
        // -------------------------------------------------------

        // ------------------- STRIPE PAYMENT --------------------
        // use stripe card element from stripe

        // only after loading the stripe js. so check that first
        if (window.Stripe) {
            var stripe = Stripe("{{ config('services.stripe.public') }}");

            // create an instance of stripe elements
            var elements = stripe.elements();
            // create an instance of stripe card elements
            var card = elements.create('card', {
                hidePostalCode: true
            });
            // add an instance of the card element into card-element div
            card.mount('#card-element');

            // display if any error cames when entering datas in card elements
            card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            // -------------------------------------------------------

            // handle stripe card element and create stripe token (payment method id)
            var submitButton = document.getElementById('buyPlanSubmitBtn');
            submitButton.addEventListener('click', function(event) {

                submitButton.innerHTML =
                    'Please Wait <i class="fa fa-spinner fa-spin" style="font-size:24px !important;"></i>';
                submitButton.setAttribute("disabled", "disabled");

                // create token by passing stripe card element means the card details are in that element
                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                        submitButton.innerHTML = 'Buy Plan';
                        submitButton.removeAttribute("disabled");

                    } else {
                        console.log(result);
                        createSubscription(result.token);
                        console.log(result.token);
                    }
                });
            });
        }

        // -------------------------------------------------------
        // ------------------ CREATE SUBSCRIPTION ----------------
        function createSubscription(token) {
            var planID = $('#planId').val();

            $.ajax({
                type: "POST",
                url: "{{ route('createSubscription') }}",
                data: {
                    planID,
                    data: token,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.reload();
                    } else {
                        alert('Something went wrong!');
                        $('$buyPlanSubmitBtn').html("Buy Plan");
                        $('$buyPlanSubmitBtn').removeAttr("disabled");
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.statusText);
                }
            });
        }
        // -------------------------------------------------------
    </script>
@endpush
