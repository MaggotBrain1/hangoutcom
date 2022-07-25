import React from 'react';
import Test from "./test";

const To = () => {

    const [pseudo, setPseudo] = useState("Tintin");
    const [isOpen, setIsOpen] = useState(false);

    useEffect(()=>{
    },[isOpen])


    return (
        <div>
            {
                isOpen ?
                        <div>
                                notre formulaire
                        </div>
                    :
                       <></>
            }
            <label htmlFor={'pseudo'}>Nom</label>
            <button type={'button'} onPress={setIsOpen(true)}>Lieu</button>
            <input name={'pseudo'} type="text" value={pseudo} onChange={setPseudo}/>
            <Test name={pseudo}/>
        </div>
    )

}

export default To;