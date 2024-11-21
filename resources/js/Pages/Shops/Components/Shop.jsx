import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import AddCustomerForm from '@/Pages/Shops/Components/Forms/AddCustomerForm';
import Customers from '@/Pages/Shops/Components/Customers';
import { usePage } from '@inertiajs/react';

export default function Shop({ shop }) {
    const { auth } = usePage().props;
    const [addCustomerModalIsOpen, setAddCustomerModalIsOpen] = useState(false);

    const handleAddCustomerButtonClick = (e) => {
        setAddCustomerModalIsOpen(true);
    };

    const closeAddCustomerModal = (() => {
        setAddCustomerModalIsOpen(false);
    });

    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
            <p>ID: {shop.id}</p>
            <p>{shop.name}</p>
            <p>Владелец: {shop.owner.name}</p>
            {shop.customers &&
                <Customers shopId={shop.id} customers={shop.customers} />
            }
            <PrimaryButton
                className="mt-4"
                onClick={(e) => handleAddCustomerButtonClick(e)}>
                Добавить пользователя
            </PrimaryButton>
            <Modal show={addCustomerModalIsOpen} onClose={closeAddCustomerModal}>
                <AddCustomerForm currentShopId={shop.id} closeModal={() => closeAddCustomerModal()} />
            </Modal>
        </div >
    );
}
