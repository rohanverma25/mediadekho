@php
    $price = $inventory->price;
@endphp

<div class="card mb-3">
    <div class="card-header">Pricing</div>
    <div class="card-body">
        @if (! $canManagePrice)
            <p class="text-muted mb-0">You don't have permission to manage pricing for this item.</p>
        @else
            <form method="POST" action="{{ route('admin.media-inventory.price.store', $inventory) }}" id="pricingForm">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Base Price</label>
                        <input type="number" step="0.01" min="0" name="base_price" id="base_price" class="form-control calc-input" value="{{ old('base_price', $price?->base_price) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">MSME Startups Markup %</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="1000" name="retail_percentage" id="retail_percentage" class="form-control calc-input" value="{{ old('retail_percentage', $price?->retail_percentage ?? 0) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">MSME Startups price: <span id="retail_price_preview">—</span></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Brand/Company Markup %</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="1000" name="b2c_percentage" id="b2c_percentage" class="form-control calc-input" value="{{ old('b2c_percentage', $price?->b2c_percentage ?? 0) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Brand/Company price: <span id="b2c_price_preview">—</span></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">B2B Markup %</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="1000" name="b2b_percentage" id="b2b_percentage" class="form-control calc-input" value="{{ old('b2b_percentage', $price?->b2b_percentage ?? 0) }}" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">B2B price: <span id="b2b_price_preview">—</span></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Custom Price</label>
                        <input type="number" step="0.01" min="0" name="enterprise_price" id="enterprise_price" class="form-control calc-input" value="{{ old('enterprise_price', $price?->enterprise_price) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Discount Type</label>
                        <select name="discount_type" id="discount_type" class="form-select calc-input">
                            <option value="">None</option>
                            <option value="flat" @selected(old('discount_type', $price?->discount_type) === 'flat')>Flat</option>
                            <option value="percentage" @selected(old('discount_type', $price?->discount_type) === 'percentage')>Percentage</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Discount Value</label>
                        <input type="number" step="0.01" min="0" name="discount_value" id="discount_value" class="form-control calc-input" value="{{ old('discount_value', $price?->discount_value) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tax %</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_percentage" id="tax_percentage" class="form-control calc-input" value="{{ old('tax_percentage', $price?->tax_percentage ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Commission %</label>
                        <input type="number" step="0.01" min="0" max="100" name="commission_percentage" id="commission_percentage" class="form-control calc-input" value="{{ old('commission_percentage', $price?->commission_percentage ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Platform Margin</label>
                        <input type="number" step="0.01" min="0" name="platform_margin" id="platform_margin" class="form-control calc-input" value="{{ old('platform_margin', $price?->platform_margin ?? 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="effective_from" class="form-control" value="{{ old('effective_from', $price?->effective_from?->toDateString()) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="effective_to" class="form-control" value="{{ old('effective_to', $price?->effective_to?->toDateString()) }}">
                    </div>
                </div>

                <hr>

                <h6 class="mb-3">Live Calculation</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-3">
                        <thead>
                            <tr>
                                <th>Tier</th>
                                <th>Price</th>
                                <th>Selling Price</th>
                                <th>Tax</th>
                                <th>Final Price</th>
                                <th>Commission</th>
                                <th>Net Profit</th>
                                <th>Margin %</th>
                            </tr>
                        </thead>
                        <tbody id="calcTableBody">
                            @foreach (['retail' => 'MSME Startups', 'b2c' => 'Brand/Company', 'b2b' => 'B2B', 'enterprise' => 'Custom'] as $tier => $label)
                                <tr data-tier="{{ $tier }}">
                                    <td class="fw-semibold">{{ $label }}</td>
                                    <td data-field="price">—</td>
                                    <td data-field="selling_price">—</td>
                                    <td data-field="tax_amount">—</td>
                                    <td data-field="final_price" class="fw-semibold">—</td>
                                    <td data-field="commission_amount">—</td>
                                    <td data-field="net_profit">—</td>
                                    <td data-field="margin_percentage">—</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary">Save Pricing</button>
            </form>
        @endif
    </div>
</div>

@if ($canManagePrice)
    @push('scripts')
    <script>
    $(function () {
        const percentageFieldMap = { retail: 'retail_percentage', b2c: 'b2c_percentage', b2b: 'b2b_percentage' };

        function num(id) {
            const v = parseFloat($('#' + id).val());
            return isNaN(v) ? 0 : v;
        }

        function money(value) {
            return '₹' + value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Mirrors App\Services\MediaInventoryService::setPrice() /
        // PricingService::calculateTier() exactly, so the preview here and the
        // backend-persisted numbers never disagree. Retail/B2C/B2B are derived
        // from base_price * (1 + markup% / 100); Enterprise stays a direct,
        // custom-negotiated price.
        function recalculate() {
            const basePrice = num('base_price');
            const discountType = $('#discount_type').val();
            const discountValue = num('discount_value');
            const taxPct = num('tax_percentage');
            const commissionPct = num('commission_percentage');
            const platformMargin = num('platform_margin');

            let discountAmount = 0;
            if (discountType === 'flat') discountAmount = discountValue;
            if (discountType === 'percentage') discountAmount = basePrice * (discountValue / 100);

            Object.keys(percentageFieldMap).forEach((tier) => {
                const percentage = num(percentageFieldMap[tier]);
                const tierPrice = Math.round((basePrice + (basePrice * percentage / 100)) * 100) / 100;
                $('#' + tier + '_price_preview').text(money(tierPrice));

                const row = $(`#calcTableBody tr[data-tier="${tier}"]`);
                updateRow(row, tierPrice, basePrice, discountAmount, taxPct, commissionPct, platformMargin);
            });

            const enterpriseRow = $('#calcTableBody tr[data-tier="enterprise"]');
            const enterprisePrice = $('#enterprise_price').val() === '' ? null : num('enterprise_price');

            if (enterprisePrice === null) {
                enterpriseRow.find('[data-field]').text('—');
            } else {
                updateRow(enterpriseRow, enterprisePrice, basePrice, discountAmount, taxPct, commissionPct, platformMargin);
            }
        }

        function updateRow(row, tierPrice, basePrice, discountAmount, taxPct, commissionPct, platformMargin) {
            const sellingPrice = tierPrice - discountAmount;
            const taxAmount = sellingPrice * (taxPct / 100);
            const finalPrice = sellingPrice + taxAmount;
            const commissionAmount = sellingPrice * (commissionPct / 100);
            const netProfit = sellingPrice - basePrice - commissionAmount - platformMargin;
            const marginPct = basePrice > 0 ? (netProfit / basePrice) * 100 : 0;

            row.find('[data-field="price"]').text(money(tierPrice));
            row.find('[data-field="selling_price"]').text(money(sellingPrice));
            row.find('[data-field="tax_amount"]').text(money(taxAmount));
            row.find('[data-field="final_price"]').text(money(finalPrice));
            row.find('[data-field="commission_amount"]').text(money(commissionAmount));
            row.find('[data-field="net_profit"]')
                .text(money(netProfit))
                .toggleClass('text-danger fw-semibold', netProfit < 0);
            row.find('[data-field="margin_percentage"]').text(marginPct.toFixed(2) + '%');
        }

        $('.calc-input').on('input change', recalculate);
        recalculate();
    });
    </script>
    @endpush
@endif
