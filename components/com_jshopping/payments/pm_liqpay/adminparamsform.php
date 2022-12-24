<?php
defined('_JEXEC') or die();
\JFactory::getLanguage()->load("com_jshopping.addon_pm_liqpay");
$orders_model = \JSFactory::getModel('orders');
?>
<div id="config">
    <fieldset class="adminform">
        <table class="admintable table table-borderless">
            <tr>
                <td class="key"><label
                            for="pm_paramspublic_key"><?php echo \JText::_('JSHOPPING_PM_LIQPAY_PUBLIC_KEY') ?></label>
                </td>
                <td>
                    <input type="text" name="pm_params[public_key]" id="pm_paramspublic_key"
                           class="inputbox form-control" size="3" value="<?php echo $pmconfigs['public_key'] ?>"/>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="1"></td>
            </tr>
            <tr>
                <td class="key"><label
                            for="pm_paramsprivate_key"><?php echo \JText::_('JSHOPPING_PM_LIQPAY_PRIVATE_KEY') ?></label>
                </td>
                <td>
                    <input type="text" name="pm_params[private_key]" id="pm_paramsprivate_key"
                           class="inputbox form-control" size="3" value="<?php echo $pmconfigs['private_key'] ?>"/>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="1"></td>
            </tr>
            <tr>
                <td class="key"><label
                            for="pm_paramscurrency"><?php echo \JText::_('JSHOPPING_PM_LIQPAY_CURRENCY') ?></label></td>
                <td>
                    <select name="pm_params[currency]" id="pm_paramscurrency" class="inputbox custom-select"
                            style="max-width: 240px;">
						<?php foreach (array('UAH', 'EUR', 'USD') as $value) { ?>
                            <option value="<?php echo $value ?>"<?php echo $value == $pmconfigs['currency'] ? ' selected="selected"' : '' ?>><?php echo $value ?></option>
						<?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="1"></td>
            </tr>
            <tr>
                <td class="key"><label for="pm_paramsrate"><?php echo \JText::_('JSHOPPING_PM_LIQPAY_RATE') ?></label>
                </td>
                <td>
                    <input type="text" name="pm_params[rate]" id="pm_paramsrate" class="inputbox form-control" size="3"
                           value="<?php echo $pmconfigs['rate'] ?>"/>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="1"></td>
            </tr>
            <tr>
                <td class="key"><label
                            for="pm_paramstransaction_pending_status"><?php echo \JText::_('JSHOPPING_PM_LIQPAY_PENDING_STATUS') ?></label>
                </td>
                <td>
					<?php echo \JHTML::_('select.genericlist', $orders_model->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class="inputbox custom-select" style="max-width:240px"', 'status_id', 'name', $pmconfigs['transaction_pending_status']) ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="1"></td>
            </tr>


            <tr>
                <td class="key"><label
                            for="pm_paramspublic_key"><?php echo \JText::_('JSHOPPING_PM_LIQPAY_RETURN_ITEM_ID') ?></label>
                </td>
                <td>
                    <input type="text" name="pm_params[return_item_id]" id="pm_paramsreturn_item_id"
                           class="inputbox form-control" size="3" value="<?php echo $pmconfigs['return_item_id'] ?>"/>
                </td>
            </tr>

            <tr>
                <td class="key"><label
                            for="pm_paramstransaction_failed_status"><?php echo \JText::_('JSHOPPING_PM_LIQPAY_FAILED_STATUS') ?></label>
                </td>
                <td>
					<?php echo \JHTML::_('select.genericlist', $orders_model->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class="inputbox custom-select" style="max-width:240px"', 'status_id', 'name', $pmconfigs['transaction_failed_status']) ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="1"></td>
            </tr>
            <tr>
                <td class="key"><label
                            for="pm_paramstransaction_complete_status"><?php echo \JText::_('JSHOPPING_PM_LIQPAY_COMPLETE_STATUS') ?></label>
                </td>
                <td>
					<?php echo \JHTML::_('select.genericlist', $orders_model->getAllOrderStatus(), 'pm_params[transaction_complete_status]', 'class="inputbox custom-select" style="max-width:240px"', 'status_id', 'name', $pmconfigs['transaction_complete_status']) ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" height="1"></td>
            </tr>
            <tr>
                <td class="key"><label
                            for="pm_paramstransaction_failed_status"><?php echo \JText::_('JSHOPPING_PM_LIQPAY_CHECK_DATA_RETURN') ?></label>
                </td>
                <td>
					<?php echo \JHTML::_('select.booleanlist', 'pm_params[check_data_return]', 'class="inputbox"', $pmconfigs['check_data_return']) ?>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="clr"></div>