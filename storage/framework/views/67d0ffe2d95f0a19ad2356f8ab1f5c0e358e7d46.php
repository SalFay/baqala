<div class="table-responsive text-center">
	<table id="table-cart" class="table table-condensed table-bordered ">
		<thead>
		<tr>
			<th>Item No</th>
			<th>Name</th>
			<th>Stock</th>
			<th>Sale</th>
			
			<th>Action</th>
		</tr>
		</thead>
		<tbody id="tbody">
		<?php $__currentLoopData = array_reverse($cart); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
			<tr class="p<?php echo e($item['id']); ?>">
				<input type="hidden"
				       value="<?php echo e($item['price']); ?>"
				       class="form-control actualCart<?php echo e($item['id']); ?>"
				       name="cart[<?php echo e($item['id']); ?>][sale_price]">
				<td><?php echo e($item['id']); ?></td>
				<td>
					<?php if($item['product']->arabic_name): ?>
						<?php echo e($item['product']->arabic_name); ?>

					<?php else: ?>
						<?php echo e($item['product']->full_name); ?>

					<?php endif; ?>
				</td>
				<td>
					<input type="number" style="text-align: center; width: 100%"
					       onblur="updateCart(<?php echo e($item['id']); ?>)"
					       value="<?php echo e($item['stock']); ?>"
					       min="1"
					       class="form-control stockCart<?php echo e($item['id']); ?>"
					       name="cart[<?php echo e($item['id']); ?>][stock]"></td>
				<td style="    display: inline-flex;
    border: none;
    align-items: center;">
					<input type="number" style="text-align: center; width: 100%"
					       value="<?php echo e($item['taxable_price']); ?>"
					       oninput="updateCart(<?php echo e($item['id']); ?>)"
					       min="1"
					       class="form-control priceCart<?php echo e($item['id']); ?>"
					       name="cart[<?php echo e($item['id']); ?>][taxable_price]">
				</td>
				<input type="hidden"
				       value="<?php echo e($item['purchase_price']); ?>"
				       class="pPriceCart<?php echo e($item['id']); ?>"
				       oninput="updateCart(<?php echo e($item['id']); ?>)"
				       name="cart[<?php echo e($item['id']); ?>][purchase_price]">
				
				<td>
					<a class='red' href='#'
					   onclick='deleteRow(<?php echo e($item['id']); ?>);'>
						<i class='ace-icon fa fa-trash bigger-130'></i></a>
				</td>
			</tr>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
		</tbody>
	</table>
</div><?php /**PATH C:\laragon\www\pos\themes/admin/order/cart.blade.php ENDPATH**/ ?>