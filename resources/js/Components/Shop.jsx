import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import Modal from '@/Components/Modal';
import AddCustomerForm from '@/Components/Forms/AddCustomerForm';
import { usePage } from '@inertiajs/react';

export default function Shop({ shop,  }) {
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
            <p>Name: {shop.name}</p>
            <p>Owner: {shop.owner.name}</p>
            {shop.owner.id === auth.user.id &&
            <PrimaryButton
                className="mt-4"
                onClick={(e) => handleAddCustomerButtonClick(e)}>
                Добавить пользователя
            </PrimaryButton>}
            <Modal show={addCustomerModalIsOpen} onClose={closeAddCustomerModal}>
                <AddCustomerForm currentShopId={shop.id} closeModal={() => closeAddCustomerModal()} />
            </Modal>
        </div>
    );
}
