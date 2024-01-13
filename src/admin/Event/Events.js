import React, { useState, useEffect } from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min';
import axios from 'axios';
import CopyToClipboard from '../CopyToClipboard';
import { useNavigate } from 'react-router';
import { FileDoneOutlined, SettingOutlined } from '@ant-design/icons';

import { Card, Empty, Flex, Image, Typography} from 'antd';
import { getEmailFromSession } from '../EmailRetrieval';
const MAX_TIMEOUT = 10000;

const Events = () => {
  const navigate = useNavigate();
  const [isLoading, setIsLoading] = useState(true);
  const [EventData, setEventData] = useState([]);
  const Email = sessionStorage.getItem('AdminEmail');
  const response = (id) => {
    navigate("/admin/Events/response", { state: { EventId: id } });
  };

  const modify = (id) => {
    navigate("/admin/Events/modify", { state: { EventId: id } });
  };

  useEffect(() => {
      const timeoutId = setTimeout(() => {
        setIsLoading(false); // Handle timeout
      }, MAX_TIMEOUT);

      // Make a request using Axios to fetch admin's Name based on the decrypted email
      axios.get(`http://localhost:8000/Admin/Events/EventInfoAdmin.php?email=${getEmailFromSession()}`)
        .then(response => {
          clearTimeout(timeoutId);
          const data = response.data.data;
          if (data) {
            setEventData(data);
          }
        })
        .catch(error => {
          console.error('Error fetching admin data:', error);
        })
        .finally(() => {
          setIsLoading(false);
        });
  }, [Email]);


  const renderEventCards = (visible) => {
    const filteredEventData = EventData.filter((item) => visible === item.visible);
  
    if (filteredEventData.length > 0) {
      return filteredEventData.map((item) => (
        <Card 
          style={{ width: 300 }}
          cover={<Image alt="example" width={'100%'} height={'200px'} src={`http://localhost:8000/Upload/${item.event_id}.png`} />}
          actions={[
            <SettingOutlined key="setting" onClick={() => modify(item.event_id)} />,
            <FileDoneOutlined key="edit" onClick={() => response(item.event_id)} />,
            <CopyToClipboard
              className="text-center w-100"
              textToCopy={`http://localhost:3000/student/Events/EventInfo/${item.event_id}`}
            />
          ]}
          hoverable
          key={item.event_id}
        >
          <Card.Meta  
            title={item.Title}
            description={
              <>
                <p>Limit: {item.Limits}</p>
                <p>Start Date: {item.Date}</p>
                <p>Last Date: {item.IntervalTime}</p>
                <p>Status: {item.Status === 'open' ? 'Open' : 'Closed'}</p>
              </>
            }
          />
        </Card>
      ));
    } else {
      return <Empty />;
    }
  };
  
  

  return (
    <>
      {isLoading ? (
        <div className='right col-10'>
          none
        </div>
      ) : (
        <div>
          <div className=" row ">
            {/* Header section remains the same */}
          </div>
          <div className='row'>
            
          <Typography.Title level={1} >Public Events:</Typography.Title>
            <Flex justify='center' align="center" wrap="wrap" gap="small">
        {renderEventCards('Public')}
      </Flex>
      <Typography.Title level={1} >Conditional Events:</Typography.Title>
            <Flex justify='center' align="center" wrap="wrap" gap="small">
        {renderEventCards('constraint')}
      </Flex>
      <Typography.Title level={1} >Private Events:</Typography.Title>
            <Flex justify='center' align="center" wrap="wrap" gap="small">
        {renderEventCards('Private')}
      </Flex>
          </div>
        </div>
      )}
    </>
  );
}

export default Events;
