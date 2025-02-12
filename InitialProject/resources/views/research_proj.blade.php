@extends('layouts.layout')
@section('content')

    <div class="container refund">
        <p>{{ trans('message.project_title') }}</p>

        <div class="table-refund table-responsive">
            <table id="example1" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th style="font-weight: bold;">{{ trans('message.serial') }}</th>
                        <th class="col-md-1" style="font-weight: bold;">{{ trans('message.year') }}</th>
                        <th class="col-md-4" style="font-weight: bold;">{{ trans('message.project_name') }} </th>
                        <th class="col-md-4" style="font-weight: bold;">{{ trans('message.details') }}</th>
                        <th class="col-md-2" style="font-weight: bold;">{{ trans('message.project_responsible') }}</th>
                        <th class="col-md-1" style="font-weight: bold;">{{ trans('message.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resp as $i => $re)
                        <tr>
                            <td style="vertical-align: top;text-align: left;">{{ $i + 1 }}</td>
                            <td style="vertical-align: top;text-align: left;">{{ $re->project_year + 543 }}</td>
                            <td style="vertical-align: top;text-align: left;">
                                {{ $re->project_name }}
                            </td>
                            <td>
                                <div style="padding-bottom: 10px">
                                    @if ($re->project_start != null)
                                        <span style="font-weight: bold;">
                                            {{ trans('message.project_duration') }}
                                        </span>
                                        <span style="padding-left: 10px;">
                                            {{ \Carbon\Carbon::parse($re->project_start)->translatedFormat('j F Y') }}
                                            {{ trans('message.to') }}
                                            {{ \Carbon\Carbon::parse($re->project_end)->translatedFormat('j F Y') }}
                                        </span>
                                    @else
                                        <span style="font-weight: bold;">
                                            {{ trans('message.project_duration') }}
                                        </span>
                                        <span></span>
                                    @endif
                                </div>

                                <!-- @if ($re->project_start != null)
    <td>{{ \Carbon\Carbon::parse($re->project_start)->thaidate('j F Y') }}<br>ถึง {{ \Carbon\Carbon::parse($re->project_end)->thaidate('j F Y') }}</td>
@else
    <td></td>
    @endif -->

                                <!-- <td>
        @foreach ($re->user as $user)
    {{ $user->position }}{{ $user->fname_th }} {{ $user->lname_th }}
        <br>
    @endforeach
                            </td> -->
                                <!-- <td>
                                @if (is_null($re->fund))
@else
    {{ $re->fund->fund_type }}
    @endif
                            </td> -->
                                <!-- <td>
        @if (is_null($re->fund))
@else
    {{ $re->fund->support_resource }}
    @endif
        </td> -->
                                <!-- <td>{{ $re->budget }}</td> -->
                                <div style="padding-bottom: 10px;">
                                    <span style="font-weight: bold;">{{ trans('message.research_type') }}</span>
                                    <span style="padding-left: 10px;">
                                        @if (is_null($re->fund))
                                        @else
                                            {{ $re->fund->fund_type }}
                                        @endif
                                    </span>
                                </div>
                                <div style="padding-bottom: 10px;">
                                    <span style="font-weight: bold;">{{ trans('message.supporting_agency') }}</span>
                                    <span style="padding-left: 10px;">
                                        @if (is_null($re->fund))
                                        @else
                                            {{ $re->fund->support_resource }}
                                        @endif
                                    </span>
                                </div>
                                <div style="padding-bottom: 10px;">
                                    <span style="font-weight: bold;">{{ trans('message.responsible_agency') }}</span>
                                    <span style="padding-left: 10px;">
                                        {{ $re->responsible_department }}
                                    </span>
                                </div>
                                <div style="padding-bottom: 10px;">
                                    <span style="font-weight: bold;">{{ trans('message.budget_allocated') }}</span>
                                    <span style="padding-left: 10px;"> {{ number_format($re->budget) }}
                                        {{ trans('message.baht') }}</span>
                                </div>
                            </td>

                            <td style="vertical-align: top;text-align: left;">
                                <div style="padding-bottom: 10px;">
                                    <span>
                                        @foreach ($re->user as $user)
                                            {{ $user->{'position_' . app()->getLocale()} ?? $user->position }}
                                            {{ $user->{'fname_' . app()->getLocale()} ?? $user->fname }}
                                            {{ $user->{'lname_' . app()->getLocale()} ?? $user->lname }}
                                            <br>
                                        @endforeach
                                    </span>
                                </div>
                            </td>
                            @if ($re->status == 1)
                                <td style="vertical-align: top;text-align: left;">
                                    <h6><label class="badge badge-success">{{ trans('message.waiting') }}</label></h6>
                                </td>
                            @elseif($re->status == 2)
                                <td style="vertical-align: top;text-align: left;">
                                    <h6><label class="badge bg-warning text-dark">{{ trans('message.progress') }}</label>
                                    </h6>
                                </td>
                            @else
                                <td style="vertical-align: top;text-align: left;">
                                    <h6><label class="badge bg-dark">{{ trans('message.closealr') }}</label>
                                        <h6>
                                </td>
                            @endif
                            <!-- <td></td>
                            <td></td> -->
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

    <script>
    $(document).ready(function() {
        var table1 = $('#example1').DataTable({
            responsive: true,
            "language": {
                "search": "{{ trans('message.search') }}",
                "lengthMenu": "{{ trans('message.show_entries') }}",
                "info": "{{ trans('message.showing_entries') }}",
                "paginate": {
                    "previous": "{{ trans('message.previous') }}",
                    "next": "{{ trans('message.next') }}"
                }
            }
        });
    });
</script>

@stop
